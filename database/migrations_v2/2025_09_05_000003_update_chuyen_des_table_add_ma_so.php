<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chuyen_des', function (Blueprint $table) {
            if (!Schema::hasColumn('chuyen_des', 'ma_so')) {
                $table->string('ma_so')->nullable()->unique()->after('id')
                      ->comment('Mã tự sinh, CD001, CD002…');
            }
        });

        // Cập nhật giá trị ma_so cho các bản ghi cũ
        $chuyen_des = DB::table('chuyen_des')->orderBy('id')->get();
        foreach ($chuyen_des as $index => $cd) {
            $ma_so = 'CD' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            DB::table('chuyen_des')->where('id', $cd->id)->update(['ma_so' => $ma_so]);
        }
    }

    public function down(): void
    {
        Schema::table('chuyen_des', function (Blueprint $table) {
            if (Schema::hasColumn('chuyen_des', 'ma_so')) {
                $table->dropColumn('ma_so');
            }
        });
    }
};
