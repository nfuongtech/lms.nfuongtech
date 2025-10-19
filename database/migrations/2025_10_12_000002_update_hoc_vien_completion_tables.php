<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chấp nhận cả 2 biến thể tên bảng: hoc_vien_hoan_thanh / hoc_vien_hoan_thanhs
        $tableName = null;
        if (Schema::hasTable('hoc_vien_hoan_thanh')) {
            $tableName = 'hoc_vien_hoan_thanh';
        } elseif (Schema::hasTable('hoc_vien_hoan_thanhs')) {
            $tableName = 'hoc_vien_hoan_thanhs';
        }

        // Nếu không có bảng nào, bỏ qua để tránh lỗi
        if (! $tableName) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            // da_duyet
            if (! Schema::hasColumn($tableName, 'da_duyet')) {
                $col = $table->boolean('da_duyet')->default(false);
                if (Schema::hasColumn($tableName, 'ghi_chu')) {
                    $col->after('ghi_chu');
                }
            }

            // ngay_duyet
            if (! Schema::hasColumn($tableName, 'ngay_duyet')) {
                $col = $table->timestamp('ngay_duyet')->nullable();
                // chỉ đặt AFTER nếu cột tham chiếu tồn tại từ trước (tránh lỗi)
                if (Schema::hasColumn($tableName, 'da_duyet')) {
                    $col->after('da_duyet');
                }
            }

            // so_chung_nhan
            if (! Schema::hasColumn($tableName, 'so_chung_nhan')) {
                $col = $table->string('so_chung_nhan')->nullable();
                if (Schema::hasColumn($tableName, 'chung_chi_tap_tin')) {
                    $col->after('chung_chi_tap_tin');
                }
            }

            // chung_chi_het_han
            if (! Schema::hasColumn($tableName, 'chung_chi_het_han')) {
                $col = $table->date('chung_chi_het_han')->nullable();
                if (Schema::hasColumn($tableName, 'so_chung_nhan')) {
                    $col->after('so_chung_nhan');
                }
            }
        });
    }

    public function down(): void
    {
        $tableName = null;
        if (Schema::hasTable('hoc_vien_hoan_thanh')) {
            $tableName = 'hoc_vien_hoan_thanh';
        } elseif (Schema::hasTable('hoc_vien_hoan_thanhs')) {
            $tableName = 'hoc_vien_hoan_thanhs';
        }

        if (! $tableName) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            foreach (['da_duyet', 'ngay_duyet', 'so_chung_nhan', 'chung_chi_het_han'] as $col) {
                if (Schema::hasColumn($tableName, $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
