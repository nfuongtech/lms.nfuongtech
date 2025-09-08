<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ket_qua_khoa_hocs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_vien_id')->constrained('hoc_viens')->cascadeOnDelete();
            $table->foreignId('khoa_hoc_id')->constrained('khoa_hocs')->cascadeOnDelete();

            $table->decimal('diem', 5, 2)->nullable(); // điểm số 0.00 -> 100.00
            $table->string('xep_loai')->nullable();    // Giỏi, Khá, Trung bình
            $table->text('nhan_xet')->nullable();      // nhận xét của giảng viên

            $table->timestamps();

            // 1 học viên chỉ có 1 kết quả trong 1 khóa học
            $table->unique(['hoc_vien_id', 'khoa_hoc_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ket_qua_khoa_hocs');
    }
};
