<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuy_chon_ket_quas', function (Blueprint $table) {
            $table->id();
            $table->string('loai'); // Sẽ lưu 'chuyen_can' hoặc 'ket_qua'
            $table->string('gia_tri'); // Sẽ lưu 'Phép', 'Không phép', 'Hoàn thành'...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuy_chon_ket_quas');
    }
};
