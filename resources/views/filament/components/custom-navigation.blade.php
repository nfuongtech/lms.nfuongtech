@php
    use App\Models\MenuItem;

    $customNavigation = MenuItem::toTree();
@endphp

@if (! empty($customNavigation))
    <div class="mt-6 px-3">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            Menu tùy chỉnh
        </p>

        <nav class="mt-3 flex flex-col gap-1">
            @foreach ($customNavigation as $item)
                <div class="flex flex-col gap-1">
                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        class="group flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-gray-600 hover:bg-primary-50 hover:text-primary-700 dark:text-gray-200 dark:hover:bg-primary-500/20"
                    >
                        @if (! empty($item['icon']))
                            <x-dynamic-component :component="$item['icon']" class="h-5 w-5" />
                        @else
                            <x-heroicon-o-link class="h-5 w-5" />
                        @endif
                        <span class="fi-sidebar-item-label flex-1 truncate">
                            {{ $item['label'] }}
                        </span>
                    </a>

                    @if (! empty($item['children']))
                        <div class="ml-6 flex flex-col gap-1 border-l border-gray-200 pl-3 dark:border-gray-700">
                            @foreach ($item['children'] as $child)
                                <a
                                    href="{{ $child['url'] ?? '#' }}"
                                    class="group flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-gray-600 hover:bg-primary-50 hover:text-primary-700 dark:text-gray-300 dark:hover:bg-primary-500/20"
                                >
                                    @if (! empty($child['icon']))
                                        <x-dynamic-component :component="$child['icon']" class="h-4 w-4" />
                                    @else
                                        <x-heroicon-o-chevron-right class="h-4 w-4" />
                                    @endif
                                    <span class="fi-sidebar-item-label flex-1 truncate">{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>
    </div>
@endif
