<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('lich_hocs', function (Blueprint $table) {
            if (!Schema::hasColumn('lich_hocs', 'buoi')) {
                $table->unsignedInteger('buoi')->nullable()->after('ngay_hoc')->comment('Số buổi trong khóa/phiên');
            }
        });
    }

    public function down()
    {
        Schema::table('lich_hocs', function (Blueprint $table) {
            if (Schema::hasColumn('lich_hocs', 'buoi')) {
                $table->dropColumn('buoi');
            }
        });
    }
};
