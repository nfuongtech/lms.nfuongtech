<?php

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('admin_navigation_items')) {
            return;
        }

        $timestamp = now();
        $groups = [];
        $groupOrder = 0;

        $this->registerDashboard($groups, $groupOrder, $timestamp);
        $this->registerResources($groups, $groupOrder, $timestamp);
    }

    public function down(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('admin_navigation_items')) {
            return;
        }

        \Illuminate\Support\Facades\DB::table('admin_navigation_items')->truncate();
    }

    protected function registerDashboard(array &$groups, int &$groupOrder, $timestamp): void
    {
        $dashboardClass = \App\Filament\Pages\Dashboard::class;

        $groupKey = 'dashboard';
        if (! isset($groups[$groupKey])) {
            $groups[$groupKey] = \Illuminate\Support\Facades\DB::table('admin_navigation_items')->insertGetId([
                'title' => 'Dashboard',
                'type' => 'group',
                'icon' => 'heroicon-o-home-modern',
                'target' => null,
                'url' => null,
                'parent_id' => null,
                'sort' => $groupOrder++,
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        if (class_exists($dashboardClass) && is_subclass_of($dashboardClass, \Filament\Pages\Page::class)) {
            \Illuminate\Support\Facades\DB::table('admin_navigation_items')->insert([
                'title' => method_exists($dashboardClass, 'getNavigationLabel')
                    ? $dashboardClass::getNavigationLabel()
                    : 'Bảng điều khiển',
                'type' => 'page',
                'icon' => method_exists($dashboardClass, 'getNavigationIcon')
                    ? $dashboardClass::getNavigationIcon()
                    : 'heroicon-o-home',
                'target' => $dashboardClass,
                'url' => null,
                'parent_id' => $groups[$groupKey],
                'sort' => 0,
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    protected function registerResources(array &$groups, int &$groupOrder, $timestamp): void
    {
        $resourcePath = app_path('Filament/Resources');

        if (! \Illuminate\Support\Facades\File::isDirectory($resourcePath)) {
            return;
        }

        $resourceFiles = \Illuminate\Support\Facades\File::allFiles($resourcePath);

        foreach ($resourceFiles as $file) {
            $relativePath = \Illuminate\Support\Str::of($file->getRelativePathname())
                ->replaceLast('.php', '')
                ->replace('/', '\\');

            $class = 'App\\Filament\\Resources\\' . $relativePath;

            if (! class_exists($class) || ! is_subclass_of($class, \Filament\Resources\Resource::class)) {
                continue;
            }

            if (method_exists($class, 'shouldRegisterNavigation') && ! $class::shouldRegisterNavigation()) {
                continue;
            }

            $groupLabel = method_exists($class, 'getNavigationGroup')
                ? $class::getNavigationGroup()
                : null;

            $groupLabel = $groupLabel ?: 'Khác';
            $groupKey = \Illuminate\Support\Str::slug($groupLabel);

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = \Illuminate\Support\Facades\DB::table('admin_navigation_items')->insertGetId([
                    'title' => $groupLabel,
                    'type' => 'group',
                    'icon' => null,
                    'target' => null,
                    'url' => null,
                    'parent_id' => null,
                    'sort' => $groupOrder++,
                    'is_active' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            \Illuminate\Support\Facades\DB::table('admin_navigation_items')->insert([
                'title' => method_exists($class, 'getNavigationLabel')
                    ? $class::getNavigationLabel()
                    : class_basename($class),
                'type' => 'resource',
                'icon' => method_exists($class, 'getNavigationIcon')
                    ? $class::getNavigationIcon()
                    : null,
                'target' => $class,
                'url' => null,
                'parent_id' => $groups[$groupKey],
                'sort' => method_exists($class, 'getNavigationSort')
                    ? (int) $class::getNavigationSort()
                    : 0,
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }
};
