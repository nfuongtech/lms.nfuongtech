<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;
use App\Models\LichHoc;

class NoOverlappingSchedules implements Rule
{
    protected $messageText = 'Có xung đột lịch (giảng viên hoặc phòng trùng giờ).';
    protected $khoaHocId; // để ignore cùng khóa học khi edit/create?

    public function __construct($khoaHocId = null)
    {
        $this->khoaHocId = $khoaHocId;
    }

    /**
     * $value là mảng repeater (nhiều buổi)
     */
    public function passes($attribute, $value)
    {
        if (!is_array($value)) return true;

        // 1) Kiểm tra xung đột nội bộ giữa các dòng trong cùng form
        $rows = $value;
        $n = count($rows);
        for ($i=0;$i<$n;$i++){
            $a = $rows[$i];
            if (empty($a['ngay_hoc']) || empty($a['gio_bat_dau']) || empty($a['gio_ket_thuc'])) continue;
            $aStart = Carbon::parse($a['ngay_hoc'].' '.$a['gio_bat_dau']);
            $aEnd = Carbon::parse($a['ngay_hoc'].' '.$a['gio_ket_thuc']);
            for ($j=$i+1;$j<$n;$j++){
                $b = $rows[$j];
                if (empty($b['ngay_hoc']) || empty($b['gio_bat_dau']) || empty($b['gio_ket_thuc'])) continue;
                $bStart = Carbon::parse($b['ngay_hoc'].' '.$b['gio_bat_dau']);
                $bEnd = Carbon::parse($b['ngay_hoc'].' '.$b['gio_ket_thuc']);
                // same date?
                if ($a['ngay_hoc'] == $b['ngay_hoc']) {
                    // check overlap
                    if ($aStart < $bEnd && $aEnd > $bStart) {
                        // if same giang_vien or same dia_diem -> conflict
                        if (!empty($a['giang_vien_id']) && !empty($b['giang_vien_id']) && $a['giang_vien_id'] == $b['giang_vien_id']) {
                            $this->messageText = "Xung đột nội bộ: Giảng viên {$a['giang_vien_id']} trùng giờ trong cùng form.";
                            return false;
                        }
                        if (!empty($a['dia_diem']) && !empty($b['dia_diem']) && $a['dia_diem'] == $b['dia_diem']) {
                            $this->messageText = "Xung đột nội bộ: Phòng {$a['dia_diem']} trùng giờ trong cùng form.";
                            return false;
                        }
                    }
                }
            }
        }

        // 2) Kiểm tra xung đột với DB (LichHoc tồn tại)
        foreach ($rows as $r) {
            if (empty($r['ngay_hoc']) || empty($r['gio_bat_dau']) || empty($r['gio_ket_thuc'])) continue;
            $start = Carbon::parse($r['ngay_hoc'].' '.$r['gio_bat_dau']);
            $end = Carbon::parse($r['ngay_hoc'].' '.$r['gio_ket_thuc']);

            // build query
            $q = LichHoc::where('ngay_hoc', $r['ngay_hoc'])
                ->where(function($qTime) use ($r, $start, $end) {
                    // overlap where gio_bat_dau < end and gio_ket_thuc > start
                    $qTime->where('gio_bat_dau', '<', $end->format('H:i:s'))
                          ->where('gio_ket_thuc', '>', $start->format('H:i:s'));
                });

            // same giang_vien
            if (!empty($r['giang_vien_id'])) {
                $q->where('giang_vien_id', $r['giang_vien_id']);
            }

            // same dia_diem (room)
            if (!empty($r['dia_diem'])) {
                $q->orWhere(function($qq) use ($r, $start, $end) {
                    $qq->where('ngay_hoc', $r['ngay_hoc'])
                       ->where('dia_diem', $r['dia_diem'])
                       ->where('gio_bat_dau', '<', $end->format('H:i:s'))
                       ->where('gio_ket_thuc', '>', $start->format('H:i:s'));
                });
            }

            if ($this->khoaHocId) {
                // ignore records belonging to same khoa_hoc (useful on edit)
                $q->where(function($qq) {
                    // wrapper - keep original conditions; but exclude same khoa_hoc_id
                });
                // easier: filter out by adding whereNot('khoa_hoc_id',$this->khoaHocId) at top-level
                $q->where('khoa_hocs_id','<>',$this->khoaHocId); // if wrong column adjust
            }

            // Execute
            if ($q->exists()) {
                $this->messageText = 'Xung đột với lịch đã có: Giảng viên/phòng trùng giờ.';
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return $this->messageText;
    }
}
