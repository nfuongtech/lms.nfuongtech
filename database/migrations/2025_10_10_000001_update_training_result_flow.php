<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('khoa_hocs', function (Blueprint $table) {
            if (! Schema::hasColumn('khoa_hocs', 'da_chuyen_ket_qua')) {
                $table->boolean('da_chuyen_ket_qua')->default(false)->after('tam_hoan');
            }

            if (! Schema::hasColumn('khoa_hocs', 'thoi_gian_chuyen_ket_qua')) {
                $table->timestamp('thoi_gian_chuyen_ket_qua')->nullable()->after('da_chuyen_ket_qua');
            }

            if (! Schema::hasColumn('khoa_hocs', 'nguoi_chuyen_ket_qua')) {
                $table->string('nguoi_chuyen_ket_qua')->nullable()->after('thoi_gian_chuyen_ket_qua');
            }
        });

        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'tong_so_gio_ke_hoach')) {
                $table->decimal('tong_so_gio_ke_hoach', 6, 2)->nullable()->after('dang_ky_id');
            }

            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'tong_so_gio_thuc_te')) {
                $table->decimal('tong_so_gio_thuc_te', 6, 2)->nullable()->after('tong_so_gio_ke_hoach');
            }

            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'diem_trung_binh')) {
                $table->decimal('diem_trung_binh', 5, 2)->nullable()->after('tong_so_gio_thuc_te');
            }

            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'ket_qua_goi_y')) {
                $table->string('ket_qua_goi_y')->nullable()->after('diem_trung_binh');
            }

            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'danh_gia_ren_luyen')) {
                $table->text('danh_gia_ren_luyen')->nullable()->after('ket_qua_goi_y');
            }

            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'needs_review')) {
                $table->boolean('needs_review')->default(false)->after('nguoi_nhap');
            }
        });

        Schema::table('hoc_vien_hoan_thanh', function (Blueprint $table) {
            if (! Schema::hasColumn('hoc_vien_hoan_thanh', 'chi_phi_dao_tao')) {
                $table->decimal('chi_phi_dao_tao', 15, 2)->nullable()->after('ngay_hoan_thanh');
            }

            if (! Schema::hasColumn('hoc_vien_hoan_thanh', 'chung_chi_link')) {
                $table->string('chung_chi_link')->nullable()->after('chi_phi_dao_tao');
            }

            if (! Schema::hasColumn('hoc_vien_hoan_thanh', 'chung_chi_tap_tin')) {
                $table->string('chung_chi_tap_tin')->nullable()->after('chung_chi_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('khoa_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('khoa_hocs', 'da_chuyen_ket_qua')) {
                $table->dropColumn(['da_chuyen_ket_qua', 'thoi_gian_chuyen_ket_qua', 'nguoi_chuyen_ket_qua']);
            }
        });

        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            $columns = [
                'tong_so_gio_ke_hoach',
                'tong_so_gio_thuc_te',
                'diem_trung_binh',
                'ket_qua_goi_y',
                'danh_gia_ren_luyen',
                'needs_review',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('ket_qua_khoa_hocs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('hoc_vien_hoan_thanh', function (Blueprint $table) {
            $columns = ['chi_phi_dao_tao', 'chung_chi_link', 'chung_chi_tap_tin'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('hoc_vien_hoan_thanh', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
