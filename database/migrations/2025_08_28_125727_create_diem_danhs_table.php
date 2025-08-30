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

            $table->foreignId('dang_ky_id')->constrained('dang_kies')->cascadeOnDelete();
            $table->foreignId('lich_hoc_id')->constrained('lich_hocs')->cascadeOnDelete();
            $table->string('trang_thai'); // Ví dụ: 'Có mặt', 'Phép', 'Không phép'
            $table->text('ly_do_vang')->nullable();

            $table->timestamps();
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
