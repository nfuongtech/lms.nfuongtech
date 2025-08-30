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
        Schema::create('chuyen_des', function (Blueprint $table) {
            $table->id();
            $table->string('ma_so')->unique();
            $table->string('ten_chuyen_de');
            $table->decimal('thoi_luong', 8, 2)->comment('Đơn vị: giờ');
            $table->string('doi_tuong_dao_tao');
            $table->text('muc_tieu')->nullable(); // Cho phép để trống
            $table->text('noi_dung')->nullable(); // Cho phép để trống
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chuyen_des');
    }
};
