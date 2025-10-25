<?php

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\Schema::create('admin_navigation_items', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('icon')->nullable();
            $table->string('target')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('admin_navigation_items')->cascadeOnDelete();
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\Schema::dropIfExists('admin_navigation_items');
    }
};
