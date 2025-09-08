<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            // thêm cột đánh dấu có mặt/vắng
            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'co_mat')) {
                $table->boolean('co_mat')->default(true)->after('dang_ky_id');
            }

            // lý do vắng (nếu vắng)
            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'ly_do_vang')) {
                $table->string('ly_do_vang')->nullable()->after('co_mat');
            }

            // người nhập / thời gian nhập (tùy hữu ích cho audit)
            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'nguoi_nhap')) {
                $table->string('nguoi_nhap')->nullable()->after('hoc_phi');
            }
            if (! Schema::hasColumn('ket_qua_khoa_hocs', 'ngay_nhap')) {
                $table->timestamp('ngay_nhap')->nullable()->after('nguoi_nhap');
            }

            // lưu trạng thái chi tiết (nếu muốn): giữ nguyên 'ket_qua' cũ
        });
    }

    public function down(): void
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'co_mat')) {
                $table->dropColumn('co_mat');
            }
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'ly_do_vang')) {
                $table->dropColumn('ly_do_vang');
            }
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'nguoi_nhap')) {
                $table->dropColumn('nguoi_nhap');
            }
            if (Schema::hasColumn('ket_qua_khoa_hocs', 'ngay_nhap')) {
                $table->dropColumn('ngay_nhap');
            }
        });
    }
};
