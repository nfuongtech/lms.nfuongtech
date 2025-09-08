<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            if (!Schema::hasColumn('ket_qua_khoa_hocs', 'co_mat')) {
                $table->unsignedTinyInteger('co_mat')->default(1);
            }
        });
    }

    public function down(): void
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'co_mat')) {
                $table->dropColumn('co_mat');
            }
        });
    }
};
