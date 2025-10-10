<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lich_hocs', function (Blueprint $table) {
            if (!Schema::hasColumn('lich_hocs', 'chuyen_de_id')) {
                $table->unsignedBigInteger('chuyen_de_id')->nullable()->after('khoa_hoc_id');
            }
            if (!Schema::hasColumn('lich_hocs', 'giang_vien_id')) {
                $table->unsignedBigInteger('giang_vien_id')->nullable()->after('chuyen_de_id');
            }
            if (!Schema::hasColumn('lich_hocs', 'dia_diem')) {
                $table->string('dia_diem', 255)->nullable()->after('gio_ket_thuc');
            }
            if (!Schema::hasColumn('lich_hocs', 'so_bai_kiem_tra')) {
                $table->unsignedInteger('so_bai_kiem_tra')->default(0)->after('dia_diem');
            }
            if (!Schema::hasColumn('lich_hocs', 'so_gio_giang')) {
                $table->decimal('so_gio_giang', 5, 2)->nullable()->after('so_bai_kiem_tra');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lich_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('lich_hocs', 'so_gio_giang')) $table->dropColumn('so_gio_giang');
            if (Schema::hasColumn('lich_hocs', 'so_bai_kiem_tra')) $table->dropColumn('so_bai_kiem_tra');
            if (Schema::hasColumn('lich_hocs', 'dia_diem')) $table->dropColumn('dia_diem');
            if (Schema::hasColumn('lich_hocs', 'giang_vien_id')) $table->dropColumn('giang_vien_id');
            if (Schema::hasColumn('lich_hocs', 'chuyen_de_id')) $table->dropColumn('chuyen_de_id');
        });
    }
};
