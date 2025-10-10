<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('khoa_hocs', function (Blueprint $table) {
            if (!Schema::hasColumn('khoa_hocs', 'tam_hoan')) {
                $table->boolean('tam_hoan')->default(false)->after('updated_at');
            }
            if (!Schema::hasColumn('khoa_hocs', 'ly_do_tam_hoan')) {
                $table->text('ly_do_tam_hoan')->nullable()->after('tam_hoan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('khoa_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('khoa_hocs', 'ly_do_tam_hoan')) {
                $table->dropColumn('ly_do_tam_hoan');
            }
            if (Schema::hasColumn('khoa_hocs', 'tam_hoan')) {
                $table->dropColumn('tam_hoan');
            }
        });
    }
};
