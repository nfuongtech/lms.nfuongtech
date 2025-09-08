<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('giang_viens', function (Blueprint $table) {
            $table->string('tinh_trang')->default('Đang giảng dạy')->after('tom_tat_kinh_nghiem');
        });
    }
    public function down(): void {
        Schema::table('giang_viens', function (Blueprint $table) {
            $table->dropColumn('tinh_trang');
        });
    }
};
