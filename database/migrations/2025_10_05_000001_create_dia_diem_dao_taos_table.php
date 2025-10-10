<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('dia_diem_dao_taos')) {
            Schema::create('dia_diem_dao_taos', function (Blueprint $table) {
                $table->id();
                $table->string('ma_phong', 50);
                $table->string('ten_phong', 255);
                $table->unsignedInteger('hv_toi_da')->default(0);
                $table->text('co_so_vat_chat')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('dia_diem_dao_taos', function (Blueprint $table) {
                if (Schema::hasColumn('dia_diem_dao_taos', 'thu_tu')) {
                    $table->dropColumn('thu_tu');
                }
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('dia_diem_dao_taos');
    }
};
