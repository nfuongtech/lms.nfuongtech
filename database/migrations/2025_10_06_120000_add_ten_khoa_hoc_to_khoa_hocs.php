<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('khoa_hocs', 'ten_khoa_hoc')) {
            Schema::table('khoa_hocs', function (Blueprint $table) {
                $table->string('ten_khoa_hoc', 255)->nullable()->after('ma_khoa_hoc');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('khoa_hocs', 'ten_khoa_hoc')) {
            Schema::table('khoa_hocs', function (Blueprint $table) {
                $table->dropColumn('ten_khoa_hoc');
            });
        }
    }
};
