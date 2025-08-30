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
            // Thêm cột điểm cho từng buổi học
            $table->decimal('diem_buoi_hoc', 4, 2)->nullable()->after('ly_do_vang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diem_danhs', function (Blueprint $table) {
            $table->dropColumn('diem_buoi_hoc');
        });
    }
};
