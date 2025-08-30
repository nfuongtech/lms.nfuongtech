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
        Schema::create('hoc_viens', function (Blueprint $table) {
            $table->id();
            $table->string('msnv')->unique();
            $table->string('ho_ten');
            $table->string('gioi_tinh')->nullable();
            $table->date('nam_sinh')->nullable();
            $table->date('ngay_vao')->nullable();
            $table->string('chuc_vu')->nullable();
            $table->foreignId('don_vi_id')->nullable()->constrained('don_vis')->nullOnDelete();
            $table->string('email')->nullable()->unique();
            $table->string('hinh_anh_path')->nullable(); // Cột mới cho hình ảnh

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoc_viens');
    }
};
