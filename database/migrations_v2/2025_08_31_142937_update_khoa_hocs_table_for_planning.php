<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('khoa_hocs', function (Blueprint $table) {
            $table->renameColumn('ten_khoa_hoc', 'ma_khoa_hoc');
            $table->dropForeign(['chuyen_de_id']);
            $table->renameColumn('chuyen_de_id', 'chuong_trinh_id');
            $table->foreign('chuong_trinh_id')->references('id')->on('chuong_trinhs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('khoa_hocs', function (Blueprint $table) {
            $table->renameColumn('ma_khoa_hoc', 'ten_khoa_hoc');
            $table->dropForeign(['chuong_trinh_id']);
            $table->renameColumn('chuong_trinh_id', 'chuyen_de_id');
            $table->foreign('chuyen_de_id')->references('id')->on('chuyen_des')->cascadeOnDelete();
        });
    }
};
