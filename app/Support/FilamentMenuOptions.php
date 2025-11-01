<?php

namespace App\Support;

use Filament\Pages\Page;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FilamentMenuOptions
{
    public static function resourceOptions(): array
    {
        return static::discoverClasses(app_path('Filament/Resources'), Resource::class)
            ->mapWithKeys(function (string $class) {
                $label = method_exists($class, 'getNavigationLabel')
                    ? $class::getNavigationLabel()
                    : Str::headline(class_basename($class));

                return [$class => $label];
            })
            ->sort()
            ->all();
    }

    public static function pageOptions(): array
    {
        return static::discoverClasses(app_path('Filament/Pages'), Page::class)
            ->reject(fn (string $class) => method_exists($class, 'shouldRegisterNavigation') && $class::shouldRegisterNavigation() === false)
            ->mapWithKeys(function (string $class) {
                $label = method_exists($class, 'getNavigationLabel')
                    ? $class::getNavigationLabel()
                    : Str::headline(class_basename($class));

                return [$class => $label];
            })
            ->sort()
            ->all();
    }

    public static function heroiconOptions(): array
    {
        $basePath = base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg/outline');

        if (! File::isDirectory($basePath)) {
            return [];
        }

        return collect(File::files($basePath))
            ->mapWithKeys(function ($file) {
                $name = $file->getFilenameWithoutExtension();
                $label = Str::of($name)->replace('-', ' ')->title();

                return ["heroicon-o-{$name}" => (string) $label];
            })
            ->sort()
            ->all();
    }

    protected static function discoverClasses(string $directory, string $mustExtend): Collection
    {
        if (! File::isDirectory($directory)) {
            return collect();
        }

        return collect(File::allFiles($directory))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.php'))
            ->map(function ($file) use ($directory) {
                $relative = Str::after($file->getPathname(), $directory . DIRECTORY_SEPARATOR);
                $class = Str::of($relative)
                    ->replace('/', '\\')
                    ->replace('.php', '')
                    ->prepend('App\\Filament\\');

                return (string) $class;
            })
            ->filter(fn (string $class) => class_exists($class))
            ->filter(fn (string $class) => is_subclass_of($class, $mustExtend))
            ->unique()
            ->values();
    }
}
