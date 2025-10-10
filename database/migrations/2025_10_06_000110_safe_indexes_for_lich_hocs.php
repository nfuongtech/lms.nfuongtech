<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected function indexExists(string $table, string $indexName): bool
    {
        $db = DB::connection()->getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(1) AS c
               FROM information_schema.statistics
              WHERE table_schema = ?
                AND table_name = ?
                AND index_name = ?',
            [$db, $table, $indexName]
        );

        return ((int)($row->c ?? 0)) > 0;
    }

    public function up(): void
    {
        // Tạo index (nếu CHƯA có), tránh lỗi "Duplicate key name"
        if (!$this->indexExists('lich_hocs', 'idx_lich_hoc_thoi_gian')) {
            Schema::table('lich_hocs', function (Blueprint $table) {
                $table->index(['ngay_hoc', 'gio_bat_dau', 'gio_ket_thuc'], 'idx_lich_hoc_thoi_gian');
            });
        }

        if (!$this->indexExists('lich_hocs', 'idx_lich_hoc_khoa')) {
            Schema::table('lich_hocs', function (Blueprint $table) {
                $table->index(['khoa_hoc_id'], 'idx_lich_hoc_khoa');
            });
        }
    }

    public function down(): void
    {
        // Xoá index an toàn (nếu tồn tại)
        Schema::table('lich_hocs', function (Blueprint $table) {
            try { $table->dropIndex('idx_lich_hoc_thoi_gian'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_lich_hoc_khoa'); } catch (\Throwable $e) {}
        });
    }
};
