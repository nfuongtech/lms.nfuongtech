<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chuyen_de_giang_vien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chuyen_de_id')->constrained()->cascadeOnDelete();
            $table->foreignId('giang_vien_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chuyen_de_giang_vien');
    }
};
