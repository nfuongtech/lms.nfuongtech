<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hoc_vien_khong_hoan_thanh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hoc_vien_id');
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('ket_qua_khoa_hoc_id');
            $table->text('ly_do_khong_hoan_thanh')->nullable();
            $table->boolean('co_the_ghi_danh_lai')->default(true);
            $table->timestamps();

            $table->foreign('hoc_vien_id')->references('id')->on('hoc_viens')->onDelete('cascade');
            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hocs')->onDelete('cascade');
            $table->foreign('ket_qua_khoa_hoc_id')->references('id')->on('ket_qua_khoa_hocs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hoc_vien_khong_hoan_thanh');
    }
};
