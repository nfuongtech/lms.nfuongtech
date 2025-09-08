<?php

use App\Enums\TrangThaiKhoaHoc;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Thay thế 'Soạn thảo' bằng 'ke_hoach'
        DB::table('khoa_hocs')
            ->where('trang_thai', 'Soạn thảo')
            ->update(['trang_thai' => TrangThaiKhoaHoc::KE_HOACH->value]);
    }

    public function down(): void
    {
        // Khôi phục nếu cần (tùy chọn)
        DB::table('khoa_hocs')
            ->where('trang_thai', TrangThaiKhoaHoc::KE_HOACH->value)
            ->update(['trang_thai' => 'Soạn thảo']);
    }
};
