<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('don_vis', function (Blueprint $table) {
            $table->renameColumn('tap_doan_don_vi', 'thaco_tdtv');
            $table->renameColumn('ban_nghiep_vu', 'cong_ty_ban_nvqt');
            $table->renameColumn('bo_phan_phong', 'phong_bo_phan');
            $table->renameColumn('noi_lam_viec', 'noi_lam_viec_chi_tiet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('don_vis', function (Blueprint $table) {
            $table->renameColumn('thaco_tdtv', 'tap_doan_don_vi');
            $table->renameColumn('cong_ty_ban_nvqt', 'ban_nghiep_vu');
            $table->renameColumn('phong_bo_phan', 'bo_phan_phong');
            $table->renameColumn('noi_lam_viec_chi_tiet', 'noi_lam_viec');
        });
    }
};
