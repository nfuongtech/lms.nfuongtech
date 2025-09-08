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
                $table->string('ma_so')->nullable()->after('id');
            }
        });

        // Cập nhật các bản ghi hiện có
        $chuyen_des = DB::table('chuyen_des')->get();
        foreach ($chuyen_des as $index => $cd) {
            $ma_so = 'CD' . str_pad($index + 1, 3, '0');
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
