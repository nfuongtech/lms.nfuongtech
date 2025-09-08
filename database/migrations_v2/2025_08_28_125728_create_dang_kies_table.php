<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dang_kies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('hoc_vien_id');
            $table->timestamps();

            // Quan hệ tới khóa học
            $table->foreign('khoa_hoc_id')
                  ->references('id')
                  ->on('khoa_hocs')
                  ->onDelete('cascade');

            // Quan hệ tới học viên
            $table->foreign('hoc_vien_id')
                  ->references('id')
                  ->on('hoc_viens')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dang_kies');
    }
};
