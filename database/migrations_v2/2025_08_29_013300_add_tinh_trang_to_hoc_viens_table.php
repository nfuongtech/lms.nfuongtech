<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoc_viens', function (Blueprint $table) {
            $table->string('tinh_trang')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('hoc_viens', function (Blueprint $table) {
            $table->dropColumn('tinh_trang');
        });
    }
};
