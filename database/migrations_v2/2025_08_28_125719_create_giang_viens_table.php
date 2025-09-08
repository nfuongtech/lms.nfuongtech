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
        Schema::create('giang_viens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ma_so')->unique();
            $table->string('ho_ten');
            $table->string('hinh_anh_path')->nullable(); // Thêm cột hình ảnh
            $table->string('gioi_tinh')->nullable();
            $table->date('nam_sinh')->nullable();
            $table->string('don_vi')->nullable(); // Đơn vị tự nhập
            $table->string('ho_khau_noi_lam_viec')->nullable();
            $table->string('trinh_do')->nullable();
            $table->string('chuyen_mon')->nullable();
            $table->integer('so_nam_kinh_nghiem')->nullable();
            $table->text('tom_tat_kinh_nghiem')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giang_viens');
    }
};
