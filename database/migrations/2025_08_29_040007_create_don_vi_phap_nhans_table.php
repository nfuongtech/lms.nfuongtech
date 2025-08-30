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
        Schema::create('don_vi_phap_nhans', function (Blueprint $table) {
            // Mã số thuế là khóa chính, kiểu chuỗi
            $table->string('ma_so_thue')->primary();
            $table->string('ten_don_vi');
            $table->text('dia_chi');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('don_vi_phap_nhans');
    }
};
