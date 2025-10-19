<?php

namespace App\Observers;

use App\Models\KhoaHoc;
use App\Models\QuyTacMaKhoa;
use App\Models\ChuongTrinh;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KhoaHocCodeObserver
{
    /**
     * Tự tìm Quy tắc dựa theo Chương trình (lấy loại hình từ CT),
     * hoặc theo quy_tac_ma_khoa_id nếu đã có.
     */
    public static function resolveRuleId(array $data): ?int
    {
        // Nếu form đã có quy_tac_ma_khoa_id (trường hợp cũ), ưu tiên luôn
        if (!empty($data['quy_tac_ma_khoa_id'])) {
            return (int) $data['quy_tac_ma_khoa_id'];
        }

        // Lấy loại hình từ Chương trình
        $loaiHinh = null;
        if (!empty($data['chuong_trinh_id'])) {
            $ct = ChuongTrinh::query()->find($data['chuong_trinh_id']);
            if ($ct) {
                foreach (['loai_hinh_dao_tao','loai_hinh','loai_dt','ma_loai_hinh','loai'] as $c) {
                    if (Schema::hasColumn($ct->getTable(), $c) && !empty($ct->{$c})) {
                        $loaiHinh = (string) $ct->{$c};
                        break;
                    }
                }
            }
        }

        $q = QuyTacMaKhoa::query();

        if ($loaiHinh) {
            // Tìm theo cột tương ứng
            $table = (new QuyTacMaKhoa)->getTable();
            foreach (['loai_hinh_dao_tao','loai_hinh','loai_dt','ma_loai_hinh','loai'] as $c) {
                if (Schema::hasColumn($table, $c)) {
                    $q2 = (clone $q)->where(DB::raw("LOWER($c)"), strtolower($loaiHinh));
                    $found = $q2->orderBy('id')->value('id');
                    if ($found) return (int) $found;
                }
            }
        }

        // Nếu có cột "mac_dinh" -> lấy quy tắc mặc định
        $table = (new QuyTacMaKhoa)->getTable();
        if (Schema::hasColumn($table, 'mac_dinh')) {
            $id = QuyTacMaKhoa::query()->where('mac_dinh', 1)->orderBy('id')->value('id');
            if ($id) return (int) $id;
        }

        // Fallback: lấy quy tắc đầu tiên
        return QuyTacMaKhoa::query()->orderBy('id')->value('id');
    }

    /**
     * Sinh mã xem trước.
     */
    public static function preview(array $data): ?string
    {
        $ruleId = self::resolveRuleId($data);
        if (!$ruleId) return null;

        $rule = QuyTacMaKhoa::query()->find($ruleId);
        if (!$rule) return null;

        $format = $rule->format ?? '{LOAI}.{NAM}.{STT3}';

        // Lấy loại hình từ Chương trình
        $loaiHinh = null;
        if (!empty($data['chuong_trinh_id'])) {
            $ct = ChuongTrinh::query()->find($data['chuong_trinh_id']);
            if ($ct) {
                foreach (['loai_hinh_dao_tao','loai_hinh','loai_dt','ma_loai_hinh','loai'] as $c) {
                    if (Schema::hasColumn($ct->getTable(), $c) && !empty($ct->{$c})) {
                        $loaiHinh = (string) $ct->{$c};
                        break;
                    }
                }
            }
        }

        if (!$loaiHinh && !empty($rule->loai_hinh_mac_dinh)) {
            $loaiHinh = (string) $rule->loai_hinh_mac_dinh;
        }

        $nam = (string)($data['nam'] ?? date('Y'));
        $loai = $loaiHinh ? (string)$loaiHinh : 'DTX';

        $stt  = self::nextSequence($nam, $loai);
        $stt2 = str_pad($stt, 2, '0', STR_PAD_LEFT);
        $stt3 = str_pad($stt, 3, '0', STR_PAD_LEFT);

        $replace = [
            '{LOAI}' => Str::upper($loai),
            '{NAM}'  => $nam,
            '{STT}'  => (string)$stt,
            '{STT2}' => $stt2,
            '{STT3}' => $stt3,
        ];

        $ma = strtr($format, $replace);
        return $ma ? preg_replace('/\s+/', '', $ma) : null;
    }

    /**
     * Tự gán ma_khoa_hoc khi chế độ tự động.
     */
    public static function generateIfNeeded(array $data): array
    {
        if (!empty($data['ma_khoa_hoc'])) return $data;

        $preview = self::preview($data);
        if (!empty($preview)) $data['ma_khoa_hoc'] = $preview;

        return $data;
    }

    protected static function nextSequence(string $nam, string $loai): int
    {
        $max = KhoaHoc::query()
            ->whereYear('created_at', intval($nam))
            ->where(function ($q) use ($nam, $loai) {
                $q->where('ma_khoa_hoc', 'like', Str::upper($loai).'.'.$nam.'.%')
                  ->orWhere('ma_khoa_hoc', 'like', Str::upper($loai).$nam.'%');
            })
            ->select(DB::raw('MAX(ma_khoa_hoc) as max_code'))
            ->first();

        $last = 0;
        if (!empty($max?->max_code) && preg_match('/(\d+)\s*$/', $max->max_code, $m)) {
            $last = intval($m[1]);
        }
        return $last + 1;
    }
}
