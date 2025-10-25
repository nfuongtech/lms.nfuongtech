<?php

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\Schema::create('admin_navigation_settings', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('sidebar_mode')->default('auto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\Schema::dropIfExists('admin_navigation_settings');
    }
};
