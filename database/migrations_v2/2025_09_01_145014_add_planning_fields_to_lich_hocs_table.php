<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lich_hocs', function (Blueprint $table) {
            $table->foreignId('chuyen_de_id')->nullable()->after('khoa_hoc_id')->constrained('chuyen_des')->nullOnDelete();
            $table->integer('tuan')->nullable()->after('dia_diem');
            $table->integer('thang')->nullable()->after('tuan');
            $table->integer('nam')->nullable()->after('thang');
        });
    }

    public function down(): void
    {
        Schema::table('lich_hocs', function (Blueprint $table) {
            $table->dropForeign(['chuyen_de_id']);
            $table->dropColumn(['chuyen_de_id', 'tuan', 'thang', 'nam']);
        });
    }
};
