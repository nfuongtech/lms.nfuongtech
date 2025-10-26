<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            // Giờ phát hành & Ngày/giờ hết hiệu lực
            $table->dateTime('publish_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            // Trạng thái: draft, active, expired
            $table->enum('status', ['draft', 'active', 'expired'])->default('draft');
            // Cờ hiển thị
            $table->boolean('is_featured')->default(false); // Tiêu điểm (luôn hiển thị ở trang chủ)
            $table->boolean('is_popup')->default(false);    // Cửa sổ tiêu điểm (popup khi vào trang chủ)
            $table->boolean('enable_marquee')->default(true); // Chuyển tiếp/auto-slide ở trang chủ
            $table->unsignedSmallInteger('scroll_speed')->default(6); // tốc độ auto-slide (giây)
            // Phương tiện
            $table->string('cover_path')->nullable(); // ảnh bìa
            $table->string('video_path')->nullable(); // file video (tuỳ chọn)
            $table->string('video_url')->nullable();  // hoặc URL video (YouTube/Vimeo…)
            $table->string('redirect_url')->nullable(); // link khi bấm chi tiết (tuỳ chọn)
            $table->timestamps();

            $table->index(['status', 'publish_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
