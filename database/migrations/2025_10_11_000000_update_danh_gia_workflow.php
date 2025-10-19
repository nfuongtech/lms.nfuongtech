<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            if (!Schema::hasColumn('ket_qua_khoa_hocs', 'da_chuyen_duyet')) {
                $table->boolean('da_chuyen_duyet')->default(false)->after('ket_qua');
            }

            if (!Schema::hasColumn('ket_qua_khoa_hocs', 'diem_trung_binh')) {
                $table->decimal('diem_trung_binh', 5, 2)->nullable()->after('diem_tong_khoa');
            }

            if (!Schema::hasColumn('ket_qua_khoa_hocs', 'tong_gio_hoc')) {
                $table->decimal('tong_gio_hoc', 6, 2)->nullable()->after('diem_trung_binh');
            }

            if (!Schema::hasColumn('ket_qua_khoa_hocs', 'danh_gia_ren_luyen')) {
                $table->text('danh_gia_ren_luyen')->nullable()->after('can_hoc_lai');
            }
        });

        Schema::table('hoc_vien_hoan_thanhs', function (Blueprint $table) {
            if (!Schema::hasColumn('hoc_vien_hoan_thanhs', 'chi_phi_dao_tao')) {
                $table->decimal('chi_phi_dao_tao', 15, 2)->nullable()->after('ngay_hoan_thanh');
            }

            if (!Schema::hasColumn('hoc_vien_hoan_thanhs', 'chung_chi_link')) {
                $table->string('chung_chi_link')->nullable()->after('chi_phi_dao_tao');
            }

            if (!Schema::hasColumn('hoc_vien_hoan_thanhs', 'chung_chi_file_path')) {
                $table->string('chung_chi_file_path')->nullable()->after('chung_chi_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'da_chuyen_duyet')) {
                $table->dropColumn('da_chuyen_duyet');
            }
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'diem_trung_binh')) {
                $table->dropColumn('diem_trung_binh');
            }
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'tong_gio_hoc')) {
                $table->dropColumn('tong_gio_hoc');
            }
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'danh_gia_ren_luyen')) {
                $table->dropColumn('danh_gia_ren_luyen');
            }
        });

        Schema::table('hoc_vien_hoan_thanhs', function (Blueprint $table) {
            if (Schema::hasColumn('hoc_vien_hoan_thanhs', 'chi_phi_dao_tao')) {
                $table->dropColumn('chi_phi_dao_tao');
            }
            if (Schema::hasColumn('hoc_vien_hoan_thanhs', 'chung_chi_link')) {
                $table->dropColumn('chung_chi_link');
            }
            if (Schema::hasColumn('hoc_vien_hoan_thanhs', 'chung_chi_file_path')) {
                $table->dropColumn('chung_chi_file_path');
            }
        });
    }
};
