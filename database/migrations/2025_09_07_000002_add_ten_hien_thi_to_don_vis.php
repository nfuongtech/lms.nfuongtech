<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('don_vis', function (Blueprint $table) {
            if (!Schema::hasColumn('don_vis', 'ten_hien_thi')) {
                $table->string('ten_hien_thi')->nullable()->after('ma_don_vi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('don_vis', function (Blueprint $table) {
            $table->dropColumn('ten_hien_thi');
        });
    }
};
