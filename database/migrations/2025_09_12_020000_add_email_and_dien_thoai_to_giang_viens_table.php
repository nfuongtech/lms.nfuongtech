<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('giang_viens', function (Blueprint $table) {
            // Nếu bảng chưa có cột email và dien_thoai thì thêm vào
            if (!Schema::hasColumn('giang_viens', 'email')) {
                $table->string('email')->nullable()->after('ho_ten');
            }
            if (!Schema::hasColumn('giang_viens', 'dien_thoai')) {
                $table->string('dien_thoai')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('giang_viens', function (Blueprint $table) {
            if (Schema::hasColumn('giang_viens', 'dien_thoai')) {
                $table->dropColumn('dien_thoai');
            }
            if (Schema::hasColumn('giang_viens', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
