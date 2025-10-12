<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --------- Bảng: khoa_hocs ----------
        if (Schema::hasTable('khoa_hocs')) {
            Schema::table('khoa_hocs', function (Blueprint $table) {
                if (! Schema::hasColumn('khoa_hocs', 'da_chuyen_ket_qua')) {
                    $table->boolean('da_chuyen_ket_qua')->default(false);
                }
                if (! Schema::hasColumn('khoa_hocs', 'thoi_gian_chuyen_ket_qua')) {
                    $table->timestamp('thoi_gian_chuyen_ket_qua')->nullable();
                }
                if (! Schema::hasColumn('khoa_hocs', 'nguoi_chuyen_ket_qua')) {
                    $table->string('nguoi_chuyen_ket_qua')->nullable();
                }
            });
        }

        // --------- Bảng: ket_qua_khoa_hocs ----------
        if (Schema::hasTable('ket_qua_khoa_hocs')) {
            Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
                if (! Schema::hasColumn('ket_qua_khoa_hocs', 'tong_so_gio_ke_hoach')) {
                    $table->decimal('tong_so_gio_ke_hoach', 6, 2)->nullable();
                }
                if (! Schema::hasColumn('ket_qua_khoa_hocs', 'tong_so_gio_thuc_te')) {
                    $table->decimal('tong_so_gio_thuc_te', 6, 2)->nullable();
                }
                if (! Schema::hasColumn('ket_qua_khoa_hocs', 'diem_trung_binh')) {
                    $table->decimal('diem_trung_binh', 5, 2)->nullable();
                }
                if (! Schema::hasColumn('ket_qua_khoa_hocs', 'ket_qua_goi_y')) {
                    $table->string('ket_qua_goi_y')->nullable();
                }
                if (! Schema::hasColumn('ket_qua_khoa_hocs', 'danh_gia_ren_luyen')) {
                    $table->text('danh_gia_ren_luyen')->nullable();
                }
                if (! Schema::hasColumn('ket_qua_khoa_hocs', 'needs_review')) {
                    $table->boolean('needs_review')->default(false);
                }
            });
        }

        // --------- Bảng: hoc_vien_hoan_thanh / hoc_vien_hoan_thanhs ----------
        $hvhtTable = null;
        if (Schema::hasTable('hoc_vien_hoan_thanh')) {
            $hvhtTable = 'hoc_vien_hoan_thanh';
        } elseif (Schema::hasTable('hoc_vien_hoan_thanhs')) {
            $hvhtTable = 'hoc_vien_hoan_thanhs';
        }

        if ($hvhtTable) {
            Schema::table($hvhtTable, function (Blueprint $table) use ($hvhtTable) {
                if (! Schema::hasColumn($hvhtTable, 'chi_phi_dao_tao')) {
                    $table->decimal('chi_phi_dao_tao', 15, 2)->nullable();
                }
                if (! Schema::hasColumn($hvhtTable, 'chung_chi_link')) {
                    $table->string('chung_chi_link')->nullable();
                }
                if (! Schema::hasColumn($hvhtTable, 'chung_chi_tap_tin')) {
                    $table->string('chung_chi_tap_tin')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // --------- Bảng: khoa_hocs ----------
        if (Schema::hasTable('khoa_hocs')) {
            Schema::table('khoa_hocs', function (Blueprint $table) {
                foreach (['da_chuyen_ket_qua', 'thoi_gian_chuyen_ket_qua', 'nguoi_chuyen_ket_qua'] as $col) {
                    if (Schema::hasColumn('khoa_hocs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        // --------- Bảng: ket_qua_khoa_hocs ----------
        if (Schema::hasTable('ket_qua_khoa_hocs')) {
            Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
                $cols = [
                    'tong_so_gio_ke_hoach',
                    'tong_so_gio_thuc_te',
                    'diem_trung_binh',
                    'ket_qua_goi_y',
                    'danh_gia_ren_luyen',
                    'needs_review',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('ket_qua_khoa_hocs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        // --------- Bảng: hoc_vien_hoan_thanh / hoc_vien_hoan_thanhs ----------
        $hvhtTable = null;
        if (Schema::hasTable('hoc_vien_hoan_thanh')) {
            $hvhtTable = 'hoc_vien_hoan_thanh';
        } elseif (Schema::hasTable('hoc_vien_hoan_thanhs')) {
            $hvhtTable = 'hoc_vien_hoan_thanhs';
        }

        if ($hvhtTable) {
            Schema::table($hvhtTable, function (Blueprint $table) use ($hvhtTable) {
                foreach (['chi_phi_dao_tao', 'chung_chi_link', 'chung_chi_tap_tin'] as $col) {
                    if (Schema::hasColumn($hvhtTable, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
