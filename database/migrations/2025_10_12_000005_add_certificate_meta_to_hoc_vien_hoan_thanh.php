<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'hoc_vien_hoan_thanhs';

    public function up(): void
    {
        // Nếu bảng chưa tồn tại (chạy trước migration tạo bảng) thì bỏ qua để không fail
        if (! Schema::hasTable($this->table)) {
            return;
        }

        $hasAnchor = Schema::hasColumn($this->table, 'chung_chi_tap_tin');
        $tableName = $this->table;

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $hasAnchor) {
            // thoi_han_chung_nhan: VARCHAR(255) NULL
            if (! Schema::hasColumn($tableName, 'thoi_han_chung_nhan')) {
                $col = $table->string('thoi_han_chung_nhan')->nullable();
                // Chỉ set AFTER khi cột mốc tồn tại để tránh lỗi MySQL
                if ($hasAnchor) {
                    $col->after('chung_chi_tap_tin');
                }
            }

            // ngay_het_han_chung_nhan: DATE NULL
            if (! Schema::hasColumn($tableName, 'ngay_het_han_chung_nhan')) {
                // Không cần AFTER -> an toàn trên mọi DB, kể cả khi cột trên vừa được thêm
                $table->date('ngay_het_han_chung_nhan')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable($this->table)) {
            return;
        }

        $tableName = $this->table;

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'ngay_het_han_chung_nhan')) {
                $table->dropColumn('ngay_het_han_chung_nhan');
            }
            if (Schema::hasColumn($tableName, 'thoi_han_chung_nhan')) {
                $table->dropColumn('thoi_han_chung_nhan');
            }
        });
    }
};
