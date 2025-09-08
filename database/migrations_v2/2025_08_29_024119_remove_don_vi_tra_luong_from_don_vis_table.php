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
        Schema::table('don_vis', function (Blueprint $table) {
            // Xóa cột không còn sử dụng
            if (Schema::hasColumn('don_vis', 'don_vi_tra_luong')) {
                $table->dropColumn('don_vi_tra_luong');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('don_vis', function (Blueprint $table) {
            // Hoàn tác lại nếu cần
            $table->string('don_vi_tra_luong')->nullable()->after('ma_don_vi');
        });
    }
};
