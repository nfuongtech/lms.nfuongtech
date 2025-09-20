<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'diem_tong_khoa')) {
                $table->renameColumn('diem_tong_khoa', 'diem');
            }

            if (!Schema::hasColumn('ket_qua_khoa_hocs', 'can_hoc_lai')) {
                $table->boolean('can_hoc_lai')->default(0)->after('ket_qua');
            }

            if (!Schema::hasColumn('ket_qua_khoa_hocs', 'hoc_phi')) {
                $table->decimal('hoc_phi', 12, 2)->nullable()->after('can_hoc_lai');
            }

            if (!Schema::hasColumn('ket_qua_khoa_hocs', 'nguoi_nhap')) {
                $table->string('nguoi_nhap')->nullable()->after('hoc_phi');
            }

            if (!Schema::hasColumn('ket_qua_khoa_hocs', 'ngay_nhap')) {
                $table->timestamp('ngay_nhap')->nullable()->after('nguoi_nhap');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'diem')) {
                $table->renameColumn('diem', 'diem_tong_khoa');
            }

            $table->dropColumn(['can_hoc_lai', 'hoc_phi', 'nguoi_nhap', 'ngay_nhap']);
        });
    }
};
