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
        Schema::table('hoc_viens', function (Blueprint $table) {
            // Thêm cột liên kết đến Đơn vị pháp nhân, sau cột don_vi_id
            $table->string('don_vi_phap_nhan_id')->nullable()->after('don_vi_id');

            // Tạo khóa ngoại
            $table->foreign('don_vi_phap_nhan_id')
                  ->references('ma_so_thue')
                  ->on('don_vi_phap_nhans')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hoc_viens', function (Blueprint $table) {
            $table->dropForeign(['don_vi_phap_nhan_id']);
            $table->dropColumn('don_vi_phap_nhan_id');
        });
    }
};
