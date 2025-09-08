<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diem_danhs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dang_ky_id');
            $table->date('ngay');
            $table->boolean('co_mat')->default(false);
            $table->timestamps();

            // Quan hệ tới đăng ký
            $table->foreign('dang_ky_id')
                  ->references('id')
                  ->on('dang_kies')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_danhs');
    }
};
