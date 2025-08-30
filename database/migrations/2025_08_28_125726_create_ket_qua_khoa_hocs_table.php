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
        Schema::create('ket_qua_khoa_hocs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dang_ky_id')->constrained('dang_kies')->cascadeOnDelete();
            $table->decimal('diem', 4, 2)->nullable();
            $table->string('ket_qua')->nullable(); // Ví dụ: 'Hoàn thành', 'Không hoàn thành'
            $table->boolean('can_hoc_lai')->default(false); // Cờ để đánh dấu cần học lại
            $table->decimal('hoc_phi', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ket_qua_khoa_hocs');
    }
};
