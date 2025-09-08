<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên hiển thị
            $table->string('email')->unique();
            $table->string('host'); // SMTP host
            $table->integer('port')->default(587);
            $table->string('username');
            $table->string('password'); // mã hóa bằng Crypt
            $table->boolean('encryption_tls')->default(true);
            $table->boolean('active')->default(true); // bật/tắt
            $table->boolean('is_default')->default(false); // mặc định
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_accounts');
    }
};
