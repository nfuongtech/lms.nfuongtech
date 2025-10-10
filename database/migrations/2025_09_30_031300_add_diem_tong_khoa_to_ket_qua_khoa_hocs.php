<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiemTongKhoaToKetQuaKhoaHocs extends Migration
{
    public function up()
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            $table->decimal('diem_tong_khoa', 10, 2)->nullable()->after('diem');
        });
    }

    public function down()
    {
        Schema::table('ket_qua_khoa_hocs', function (Blueprint $table) {
            $table->dropColumn('diem_tong_khoa');
        });
    }
}
