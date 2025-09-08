<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('diem_danhs', function (Blueprint $table) {
            // Thêm cột ly_do_vang nếu chưa tồn tại
            if (!Schema::hasColumn('diem_danhs', 'ly_do_vang')) {
                $table->string('ly_do_vang')->nullable()->after('co_mat');
            }

            // Thêm cột diem_buoi_hoc sau ly_do_vang
            if (!Schema::hasColumn('diem_danhs', 'diem_buoi_hoc')) {
                $table->decimal('diem_buoi_hoc', 4, 2)->nullable()->after('ly_do_vang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diem_danhs', function (Blueprint $table) {
            if (Schema::hasColumn('diem_danhs', 'diem_buoi_hoc')) {
                $table->dropColumn('diem_buoi_hoc');
            }

            if (Schema::hasColumn('diem_danhs', 'ly_do_vang')) {
                $table->dropColumn('ly_do_vang');
            }
        });
    }
};
