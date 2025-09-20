protected function schedule(Schedule $schedule)
{
    // Chạy mỗi 15 phút / hoặc mỗi ngày 00:05 tùy Sư phụ
    $schedule->command('khoahoc:sync-status')->dailyAt('00:05');
}
