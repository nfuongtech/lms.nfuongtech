<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quy_tac_ma_khoas', function (Blueprint $table) {
            $table->id();
            $table->string('loai_hinh_dao_tao')->unique();
            $table->string('tien_to')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quy_tac_ma_khoas');
    }
};
