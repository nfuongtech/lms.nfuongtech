<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('ten_mau'); // Tên mẫu để dễ nhận biết, ví dụ: "Thông báo ban hành kế hoạch"
            $table->string('loai_thong_bao')->index(); // Ví dụ: 'ban_hanh', 'thay_doi', 'tam_hoan'
            $table->string('tieu_de');
            $table->longText('noi_dung');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('email_templates');
    }
};
