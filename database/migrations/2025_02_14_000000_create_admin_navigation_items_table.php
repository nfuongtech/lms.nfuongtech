<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('handle')->unique();
            $table->string('type');
            $table->string('target')->nullable();
            $table->string('external_url')->nullable();
            $table->string('navigation_group')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('admin_navigation_items')->cascadeOnDelete();
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('icon_source')->default('heroicon');
            $table->string('icon_name')->nullable();
            $table->string('icon_path')->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('admin_navigation_items')->insert([
            'title' => 'Tùy chỉnh Menu',
            'handle' => 'tuy-chinh-menu',
            'type' => 'resource',
            'target' => \App\Filament\Resources\AdminNavigationItemResource::class,
            'navigation_group' => 'Thiết lập',
            'sort' => 0,
            'is_active' => true,
            'icon_source' => 'heroicon',
            'icon_name' => 'heroicon-o-bars-3-center-left',
            'icon_path' => null,
            'open_in_new_tab' => false,
            'meta' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_navigation_items');
    }
};
