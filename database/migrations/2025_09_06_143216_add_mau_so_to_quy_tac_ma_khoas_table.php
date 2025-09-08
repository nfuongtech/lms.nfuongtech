<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quy_tac_ma_khoas', function (Blueprint $table) {
            $table->string('mau_so')->nullable()->after('tien_to');
        });
    }

    public function down(): void
    {
        Schema::table('quy_tac_ma_khoas', function (Blueprint $table) {
            $table->dropColumn('mau_so');
        });
    }
};
