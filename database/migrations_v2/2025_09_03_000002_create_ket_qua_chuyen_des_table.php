<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ket_qua_chuyen_des', function (Blueprint $table) {
            $table->id();

            // liên kết đến ket_qua_khoa_hocs
            $table->foreignId('ket_qua_khoa_hoc_id')
                  ->constrained('ket_qua_khoa_hocs')
                  ->cascadeOnDelete();

            // liên kết tham chiếu đến chuyên đề (nếu có), hoặc lưu snapshot tên chuyên đề
            $table->foreignId('chuyen_de_id')->nullable()->constrained('chuyen_des')->nullOnDelete();
            $table->string('ten_chuyen_de')->nullable(); // snapshot tên chuyên đề tại thời điểm nhập

            // nếu muốn liên kết đến buổi học kế hoạch
            $table->foreignId('lich_hoc_id')->nullable()->constrained('lich_hocs')->nullOnDelete();

            // điểm chuyên đề
            $table->decimal('diem', 5, 2)->nullable();

            // trạng thái chuyên đề (Có mặt / Vắng có phép / Vắng không phép)
            $table->string('trang_thai')->nullable();

            // lý do vắng (nếu vắng)
            $table->string('ly_do_vang')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ket_qua_chuyen_des');
    }
};
