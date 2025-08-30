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
        Schema::table('giang_viens', function (Blueprint $table) {
            // Thay đổi kiểu dữ liệu của cột nam_sinh thành date
            $table->date('nam_sinh')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('giang_viens', function (Blueprint $table) {
            // Hoàn tác lại thay đổi nếu cần
            $table->year('nam_sinh')->nullable()->change();
        });
    }
};
