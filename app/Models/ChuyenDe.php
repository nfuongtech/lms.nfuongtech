<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ChuyenDe extends Model
{
    use HasFactory;

    protected $fillable = [
        'ma_so',
        'ten_chuyen_de',
        'thoi_luong',
        'doi_tuong_dao_tao',
        'muc_tieu',
        'noi_dung',
        'trang_thai_tai_lieu',
        'bai_giang_path',
    ];

    protected $casts = [
        'bai_giang_path' => 'array',
        // Lưu vào DB với 2 chữ số thập phân (migrate định nghĩa decimal(8,2))
        'thoi_luong' => 'decimal:2',
    ];

    public function giangViens(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\GiangVien::class, 'chuyen_de_giang_vien');
    }

    public function chuongTrinhs(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\ChuongTrinh::class, 'chuong_trinh_chuyen_de');
    }

    public function lichHocs(): HasMany
    {
        return $this->hasMany(\App\Models\LichHoc::class, 'chuyen_de_id');
    }

    /**
     * Mutator: trước khi lưu, chuẩn hóa dấu phẩy -> dấu chấm,
     * và format thành 2 chữ số thập phân để phù hợp với cột DECIMAL(,2).
     */
    public function setThoiLuongAttribute($value)
    {
        if ($value === null || $value === '') {
            $this->attributes['thoi_luong'] = null;
            return;
        }

        // chấp nhận cả dấu phẩy hoặc chấm; chuyển sang chấm
        $normalized = str_replace(',', '.', (string)$value);
        // giữ lại chữ số và dấu chấm
        $normalized = preg_replace('/[^0-9.]/', '', $normalized);

        if ($normalized === '') {
            $this->attributes['thoi_luong'] = null;
            return;
        }

        // Lưu với 2 chữ số thập phân (DB là decimal(,2))
        $this->attributes['thoi_luong'] = number_format((float)$normalized, 2, '.', '');
    }

    /**
     * Sau khi model được saved: nếu có file upload và tên file chứa placeholder (ví dụ CD hoặc UNSAVED),
     * đổi tên file theo format: yyyymmdd-MaSo_0x.ext
     * => cập nhật lại bai_giang_path và lưu quietly để tránh vòng lặp event.
     */
    protected static function booted()
    {
        static::saved(function (ChuyenDe $model) {
            try {
                if (empty($model->bai_giang_path) || !is_array($model->bai_giang_path)) {
                    return;
                }

                $disk = config('filesystems.default');
                $changed = false;
                $newPaths = [];

                foreach ($model->bai_giang_path as $index => $path) {
                    // path thường dạng 'bai-giang/filename.ext'
                    $basename = basename($path);
                    $ext = pathinfo($basename, PATHINFO_EXTENSION) ?: '';
                    $desiredPrefix = now()->format('Ymd') . '-' . $model->ma_so . '_0' . ($index + 1);
                    $newName = $desiredPrefix . ($ext ? '.' . $ext : '');
                    $currentFullPath = $path;
                    $newFullPath = trim('bai-giang/' . $newName, '/');

                    // nếu tên khác và file tồn tại trên disk => move
                    if ($basename !== $newName && Storage::disk($disk)->exists($currentFullPath)) {
                        // tạo folder nếu cần (Storage::move sẽ tự tạo folder đích nếu driver là local)
                        Storage::disk($disk)->move($currentFullPath, $newFullPath);
                        $newPaths[] = $newFullPath;
                        $changed = true;
                    } else {
                        // giữ nguyên
                        $newPaths[] = $path;
                    }
                }

                if ($changed) {
                    // cập nhật và lưu lại quietly (không trigger events)
                    $model->bai_giang_path = $newPaths;
                    $model->saveQuietly();
                }
            } catch (\Throwable $e) {
                // Không ném lỗi ảnh hưởng UX; có thể log nếu cần
                \Log::error('ChuyenDe saved hook error renaming files: ' . $e->getMessage());
            }
        });
    }

    /**
     * Helper: trả về mảng URL (Storage::url) để view dễ dùng
     */
    public function getBaiGiangUrlsAttribute(): array
    {
        $disk = config('filesystems.default');
        $urls = [];
        if (!empty($this->bai_giang_path) && is_array($this->bai_giang_path)) {
            foreach ($this->bai_giang_path as $p) {
                if (Storage::disk($disk)->exists($p)) {
                    $urls[] = Storage::disk($disk)->url($p);
                } else {
                    // nếu $p là đã là URL, giữ nguyên
                    $urls[] = $p;
                }
            }
        }
        return $urls;
    }
}
