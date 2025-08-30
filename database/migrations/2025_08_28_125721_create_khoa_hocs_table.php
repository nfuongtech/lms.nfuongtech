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
        Schema::create('khoa_hocs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chuyen_de_id')->constrained('chuyen_des')->cascadeOnDelete();
            $table->string('ten_khoa_hoc');
            $table->year('nam');
            $table->string('trang_thai')->default('Soạn thảo'); // Dòng quan trọng đã được thêm vào
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khoa_hocs');
    }
};
