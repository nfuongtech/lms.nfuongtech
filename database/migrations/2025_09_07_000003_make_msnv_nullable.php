<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Thực hiện ALTER TABLE bằng raw SQL (không cần doctrine/dbal)
        // Điều chỉnh kích thước nếu cần (VARCHAR(191) hoặc 255)
        DB::statement("ALTER TABLE `hoc_viens` MODIFY `msnv` VARCHAR(255) NULL");
    }

    public function down(): void
    {
        // Quay về NOT NULL (không khuyến nghị nếu đã có dữ liệu)
        DB::statement("ALTER TABLE `hoc_viens` MODIFY `msnv` VARCHAR(255) NOT NULL");
    }
};
