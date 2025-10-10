<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function indexExists(string $table, string $index): bool
    {
        $db = DB::getDatabaseName();

        $row = DB::table('information_schema.STATISTICS')
            ->select('INDEX_NAME')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->first();

        return (bool) $row;
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }

    public function up(): void
    {
        // === lich_hocs: thêm cột nếu chưa có ===
        Schema::table('lich_hocs', function (Blueprint $table) {
            if (!Schema::hasColumn('lich_hocs', 'so_bai_kiem_tra')) {
                $table->unsignedInteger('so_bai_kiem_tra')->default(0)->after('dia_diem');
            }
            if (!Schema::hasColumn('lich_hocs', 'so_gio_giang')) {
                $table->decimal('so_gio_giang', 5, 2)->nullable()->after('so_bai_kiem_tra');
            }
        });

        // === lich_hocs: thêm index nếu chưa có ===
        if (!$this->indexExists('lich_hocs', 'idx_lich_hoc_thoi_gian')) {
            Schema::table('lich_hocs', function (Blueprint $table) {
                $table->index(['ngay_hoc','gio_bat_dau','gio_ket_thuc'], 'idx_lich_hoc_thoi_gian');
            });
        }
        if (!$this->indexExists('lich_hocs', 'idx_lich_hoc_gv')) {
            Schema::table('lich_hocs', function (Blueprint $table) {
                $table->index(['giang_vien_id','ngay_hoc'], 'idx_lich_hoc_gv');
            });
        }
        if (!$this->indexExists('lich_hocs', 'idx_lich_hoc_dia_diem')) {
            Schema::table('lich_hocs', function (Blueprint $table) {
                $table->index(['dia_diem','ngay_hoc'], 'idx_lich_hoc_dia_diem');
            });
        }

        // === khoa_hocs: thêm ngưỡng nếu chưa có ===
        Schema::table('khoa_hocs', function (Blueprint $table) {
            if (!Schema::hasColumn('khoa_hocs', 'yeu_cau_phan_tram_gio')) {
                $table->decimal('yeu_cau_phan_tram_gio', 5, 2)->default(80.00)->after('trang_thai');
            }
            if (!Schema::hasColumn('khoa_hocs', 'yeu_cau_diem_tb')) {
                $table->decimal('yeu_cau_diem_tb', 4, 2)->default(5.00)->after('yeu_cau_phan_tram_gio');
            }
        });

        // === Backfill so_gio_giang nếu null ===
        DB::statement("
            UPDATE lich_hocs
            SET so_gio_giang = TIME_TO_SEC(TIMEDIFF(gio_ket_thuc, gio_bat_dau))/3600.0
            WHERE so_gio_giang IS NULL
              AND gio_bat_dau IS NOT NULL
              AND gio_ket_thuc IS NOT NULL
        ");
    }

    public function down(): void
    {
        // === lich_hocs: drop index nếu đang có ===
        $this->dropIndexIfExists('lich_hocs', 'idx_lich_hoc_thoi_gian');
        $this->dropIndexIfExists('lich_hocs', 'idx_lich_hoc_gv');
        $this->dropIndexIfExists('lich_hocs', 'idx_lich_hoc_dia_diem');

        // === lich_hocs: drop cột nếu đang có ===
        Schema::table('lich_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('lich_hocs', 'so_bai_kiem_tra')) {
                $table->dropColumn('so_bai_kiem_tra');
            }
            if (Schema::hasColumn('lich_hocs', 'so_gio_giang')) {
                $table->dropColumn('so_gio_giang');
            }
        });

        // === khoa_hocs: drop cột nếu đang có ===
        Schema::table('khoa_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('khoa_hocs', 'yeu_cau_phan_tram_gio')) {
                $table->dropColumn('yeu_cau_phan_tram_gio');
            }
            if (Schema::hasColumn('khoa_hocs', 'yeu_cau_diem_tb')) {
                $table->dropColumn('yeu_cau_diem_tb');
            }
        });
    }
};
