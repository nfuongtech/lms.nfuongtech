<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoc_vien_hoan_thanh', function (Blueprint $table) {
            if (! Schema::hasColumn('hoc_vien_hoan_thanh', 'da_duyet')) {
                $table->boolean('da_duyet')->default(false)->after('ghi_chu');
            }

            if (! Schema::hasColumn('hoc_vien_hoan_thanh', 'ngay_duyet')) {
                $table->timestamp('ngay_duyet')->nullable()->after('da_duyet');
            }

            if (! Schema::hasColumn('hoc_vien_hoan_thanh', 'so_chung_nhan')) {
                $table->string('so_chung_nhan')->nullable()->after('chung_chi_tap_tin');
            }

            if (! Schema::hasColumn('hoc_vien_hoan_thanh', 'chung_chi_het_han')) {
                $table->date('chung_chi_het_han')->nullable()->after('so_chung_nhan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hoc_vien_hoan_thanh', function (Blueprint $table) {
            $columns = ['da_duyet', 'ngay_duyet', 'so_chung_nhan', 'chung_chi_het_han'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('hoc_vien_hoan_thanh', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
