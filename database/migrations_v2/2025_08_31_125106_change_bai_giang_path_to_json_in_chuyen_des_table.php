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
        Schema::table('chuyen_des', function (Blueprint $table) {
            // Thay đổi kiểu dữ liệu của cột thành JSON để lưu nhiều file
            $table->json('bai_giang_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chuyen_des', function (Blueprint $table) {
            // Hoàn tác lại thay đổi nếu cần
            $table->string('bai_giang_path')->nullable()->change();
        });
    }
};
