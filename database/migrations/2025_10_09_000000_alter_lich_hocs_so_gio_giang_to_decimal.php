<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Đổi INT -> DECIMAL(4,1) để nhập số lẻ giờ (ví dụ 3.5)
        DB::statement('ALTER TABLE lich_hocs MODIFY so_gio_giang DECIMAL(4,1) NOT NULL');
    }

    public function down(): void
    {
        // Trả về INT nếu rollback
        DB::statement('ALTER TABLE lich_hocs MODIFY so_gio_giang INT NOT NULL');
    }
};
