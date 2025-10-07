<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // % giờ học:整数 (0..100) → unsignedSmallInteger
        if (Schema::hasColumn('khoa_hocs', 'yeu_cau_phan_tram_gio')) {
            DB::statement("ALTER TABLE `khoa_hocs` MODIFY `yeu_cau_phan_tram_gio` SMALLINT UNSIGNED");
        }

        // điểm TB: đúng 1 số lẻ → DECIMAL(3,1)
        if (Schema::hasColumn('khoa_hocs', 'yeu_cau_diem_tb')) {
            DB::statement("ALTER TABLE `khoa_hocs` MODIFY `yeu_cau_diem_tb` DECIMAL(3,1)");
        }

        // Bổ sung cột tên khóa học nếu chưa có
        if (!Schema::hasColumn('khoa_hocs', 'ten_khoa_hoc')) {
            Schema::table('khoa_hocs', function (Blueprint $table) {
                $table->string('ten_khoa_hoc', 255)->nullable()->after('ma_khoa_hoc');
            });
        }
    }

    public function down(): void
    {
        // Không rollback kiểu dữ liệu để an toàn
    }
};
