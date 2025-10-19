<?php
// app/Console/Commands/SyncKhoaHocStatus.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KhoaHoc;
use Carbon\Carbon;

class SyncKhoaHocStatus extends Command
{
    protected $signature = 'khoahoc:sync-status';
    protected $description = 'Đồng bộ trạng thái khóa học (Đang đào tạo / Kết thúc) theo lich_hocs';

    public function handle()
    {
        $this->info('Bắt đầu sync trạng thái khoá học...');
        $now = Carbon::now();

        KhoaHoc::with('lichHocs')->chunkById(100, function($khBatch) use ($now) {
            foreach ($khBatch as $kh) {
                $lichs = $kh->lichHocs->sortBy('ngay_hoc');
                if ($lichs->isEmpty()) continue;
                $first = Carbon::parse($lichs->first()->ngay_hoc)->startOfDay();
                $last = Carbon::parse($lichs->last()->ngay_hoc)->endOfDay();

                $current = $kh->trang_thai;
                if ($now->between($first, $last)) {
                    if (in_array($current, ['Ban hành','Đang đào tạo']) && $current !== 'Đang đào tạo') {
                        $kh->update(['trang_thai' => 'Đang đào tạo']);
                        $this->info("KH {$kh->ma_khoa_hoc} => Đang đào tạo");
                    }
                } elseif ($now->greaterThan($last)) {
                    if ($current !== 'Kết thúc') {
                        $kh->update(['trang_thai' => 'Kết thúc']);
                        $this->info("KH {$kh->ma_khoa_hoc} => Kết thúc");
                    }
                }
            }
        });

        $this->info('Hoàn tất.');
    }
}
