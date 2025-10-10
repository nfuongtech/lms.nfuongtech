<?php

namespace App\Observers;

use App\Models\KhoaHoc;
use Carbon\Carbon;

class KhoaHocObserver
{
    /**
     * Trước khi lưu (saving) — cập nhật trạng thái thông minh.
     */
    public function saving(KhoaHoc $khoaHoc): void
    {
        // Lấy min/max ngày của lịch (nếu có)
        try {
            $first = $khoaHoc->lichHocs()->orderBy('ngay_hoc')->first();
            $last  = $khoaHoc->lichHocs()->orderByDesc('ngay_hoc')->first();
        } catch (\Throwable $e) {
            $first = null;
            $last = null;
        }

        $now = Carbon::now();

        if (! $first) {
            // Không có buổi => giữ nguyên (hoặc thiết lập mặc định)
            return;
        }

        // Nếu chưa tới buổi đầu => 'Kế hoạch'
        if ($now->lt(Carbon::parse($first->ngay_hoc))) {
            $khoaHoc->trang_thai = 'Kế hoạch';
            return;
        }

        // Nếu đã qua buổi cuối => 'Kết thúc'
        if ($last && $now->gt(Carbon::parse($last->ngay_hoc))) {
            $khoaHoc->trang_thai = 'Kết thúc';
            return;
        }

        // Ngược lại đang trong khoảng => 'Đang đào tạo'
        $khoaHoc->trang_thai = 'Đang đào tạo';
    }
}
