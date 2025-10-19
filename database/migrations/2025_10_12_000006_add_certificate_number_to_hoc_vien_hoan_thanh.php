<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['hoc_vien_hoan_thanh', 'hoc_vien_hoan_thanhs'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'so_chung_nhan')) {
                    $table->string('so_chung_nhan')->nullable()->after('chung_chi_tap_tin');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['hoc_vien_hoan_thanh', 'hoc_vien_hoan_thanhs'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'so_chung_nhan')) {
                    $table->dropColumn('so_chung_nhan');
                }
            });
        }
    }
};
