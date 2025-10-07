<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lich_hocs', function (Blueprint $table) {
            if (!Schema::hasColumn('lich_hocs','dia_diem_id')) {
                $table->unsignedBigInteger('dia_diem_id')->nullable()->after('giang_vien_id');
            }
            if (!Schema::hasColumn('lich_hocs','tuan')) {
                $table->unsignedInteger('tuan')->nullable()->after('ngay_hoc');
            }
            if (!Schema::hasColumn('lich_hocs','so_bai_kiem_tra')) {
                $table->unsignedInteger('so_bai_kiem_tra')->default(0)->after('dia_diem_id');
            }
            if (Schema::hasColumn('lich_hocs','so_gio_giang')) {
                try { $table->integer('so_gio_giang')->change(); } catch (\Throwable $e) {}
            } else {
                $table->integer('so_gio_giang')->nullable()->after('so_bai_kiem_tra');
            }
        });
    }
    public function down(): void
    {
        Schema::table('lich_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('lich_hocs','so_gio_giang')) $table->dropColumn('so_gio_giang');
            if (Schema::hasColumn('lich_hocs','so_bai_kiem_tra')) $table->dropColumn('so_bai_kiem_tra');
            if (Schema::hasColumn('lich_hocs','tuan')) $table->dropColumn('tuan');
            if (Schema::hasColumn('lich_hocs','dia_diem_id')) $table->dropColumn('dia_diem_id');
        });
    }
};
