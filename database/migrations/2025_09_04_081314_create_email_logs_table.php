<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khoa_hoc_id')->nullable()->constrained('khoa_hocs')->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('status'); // 'Thành công' hoặc 'Thất bại'
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('email_logs');
    }
};
