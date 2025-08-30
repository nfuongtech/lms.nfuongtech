<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('don_vis', function (Blueprint $table) {
            $table->id();
            $table->string('ma_don_vi')->unique();
            $table->string('bo_phan_phong')->nullable();
            $table->string('ban_nghiep_vu')->nullable();
            $table->string('tap_doan_don_vi');
            $table->string('noi_lam_viec')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('don_vis');
    }
};
