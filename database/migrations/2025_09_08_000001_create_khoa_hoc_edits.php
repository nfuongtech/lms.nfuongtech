<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('khoa_hoc_edits')) {
            Schema::create('khoa_hoc_edits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('khoa_hoc_id')->constrained('khoa_hocs')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('changes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('khoa_hoc_edits');
    }
};
