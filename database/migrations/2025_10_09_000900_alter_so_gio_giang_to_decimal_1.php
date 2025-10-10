<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 999.9 là đủ; nếu muốn rộng hơn đổi DECIMAL(5,1)
        DB::statement('ALTER TABLE `lich_hocs` MODIFY `so_gio_giang` DECIMAL(4,1) NULL');
    }

    public function down(): void
    {
        // quay lại INT nếu cần
        DB::statement('ALTER TABLE `lich_hocs` MODIFY `so_gio_giang` INT NULL');
    }
};
