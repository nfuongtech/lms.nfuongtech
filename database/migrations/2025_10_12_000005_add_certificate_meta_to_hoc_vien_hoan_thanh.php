<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoc_vien_hoan_thanh', function (Blueprint $table) {
            if (! Schema::hasColumn('hoc_vien_hoan_thanh', 'thoi_han_chung_nhan')) {
                $table->string('thoi_han_chung_nhan')->nullable()->after('chung_chi_tap_tin');
            }

            if (! Schema::hasColumn('hoc_vien_hoan_thanh', 'ngay_het_han_chung_nhan')) {
                $table->date('ngay_het_han_chung_nhan')->nullable()->after('thoi_han_chung_nhan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hoc_vien_hoan_thanh', function (Blueprint $table) {
            foreach (['thoi_han_chung_nhan', 'ngay_het_han_chung_nhan'] as $column) {
                if (Schema::hasColumn('hoc_vien_hoan_thanh', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
