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
        Schema::create('diem_danhs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dang_ky_id');
            $table->unsignedBigInteger('lich_hoc_id');
            
            // --- SỬA: Dùng varchar thay vì enum, và có default ---
            $table->string('trang_thai')->default('Có mặt'); // Mặc định là "Có mặt"
            // --- HẾT SỬA: Dùng varchar thay vì enum, và có default ---
            
            $table->text('ly_do_vang')->nullable();
            $table->decimal('diem_buoi_hoc', 5, 2)->nullable(); // decimal(5,2) theo laravel_db.pdf
            
            // --- THÊM: Cột Số giờ học ---
            $table->decimal('so_gio_hoc', 8, 2)->nullable(); // decimal(8,2) để chứa số giờ lớn
            // --- HẾT THÊM: Cột Số giờ học ---
            
            // --- THÊM: Cột Đánh giá kỷ luật ---
            $table->longText('danh_gia_ky_luat')->nullable(); // longText cho văn bản dài
            // --- HẾT THÊM: Cột Đánh giá kỷ luật ---
            
            $table->timestamps();

            // --- BẮT ĐẦU: Ràng buộc khóa ngoại ---
            $table->foreign('dang_ky_id')->references('id')->on('dang_kies')->onDelete('cascade');
            $table->foreign('lich_hoc_id')->references('id')->on('lich_hocs')->onDelete('cascade');
            // --- KẾT THÚC: Ràng buộc khóa ngoại ---

            // --- THÊM: Unique constraint để tránh trùng lặp ---
            $table->unique(['dang_ky_id', 'lich_hoc_id']); // Mỗi học viên chỉ điểm danh 1 lần/buổi
            // --- HẾT THÊM: Unique constraint để tránh trùng lặp ---
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diem_danhs');
    }
};
