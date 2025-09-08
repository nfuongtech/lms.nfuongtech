<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('khoa_hocs', function (Blueprint $table) {
            if (!Schema::hasColumn('khoa_hocs', 'ma_khoa')) {
                $table->string('ma_khoa')->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('khoa_hocs', function (Blueprint $table) {
            $table->dropColumn('ma_khoa');
        });
    }
};
