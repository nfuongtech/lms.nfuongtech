<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('diem_danh_buoi_hocs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dang_ky_id');
            $table->unsignedBigInteger('lich_hoc_id');
            $table->enum('trang_thai', ['co_mat', 'vang_phep', 'vang_khong_phep'])->default('co_mat');
            $table->text('ly_do_vang')->nullable();
            $table->decimal('diem_buoi_hoc', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('dang_ky_id')->references('id')->on('dang_kies')->onDelete('cascade');
            $table->foreign('lich_hoc_id')->references('id')->on('lich_hocs')->onDelete('cascade');

            $table->unique(['dang_ky_id', 'lich_hoc_id']); // Mỗi học viên chỉ điểm danh 1 lần/buổi
        });
    }

    public function down()
    {
        Schema::dropIfExists('diem_danh_buoi_hocs');
    }
};
