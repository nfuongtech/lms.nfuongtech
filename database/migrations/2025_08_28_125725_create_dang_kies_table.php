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
        Schema::create('dang_kies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_vien_id')->constrained('hoc_viens')->cascadeOnDelete();
            $table->foreignId('khoa_hoc_id')->constrained('khoa_hocs')->cascadeOnDelete();
            $table->timestamps(); // Đảm bảo dòng này chỉ xuất hiện một lần
            $table->unique(['hoc_vien_id', 'khoa_hoc_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dang_kies');
    }
};
