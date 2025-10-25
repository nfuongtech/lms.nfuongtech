<div
    x-data="sidebarModeManager({ initialMode: @js($mode) })"
    x-init="init()"
    class="fi-sidebar-mode-switcher hidden items-center gap-2 rounded-lg border border-gray-200 bg-white/80 px-3 py-2 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-gray-900/5 backdrop-blur dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-200 lg:flex"
>
    <span class="uppercase tracking-wide">Sidebar</span>
    <div class="flex items-center gap-1">
        @foreach (\App\Models\AdminSidebarPreference::MODES as $option)
            <button
                type="button"
                x-on:click="changeMode('{{ $option }}')"
                x-bind:class="mode === '{{ $option }}' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'"
                class="rounded-md px-2 py-1 transition"
            >
                {{ __(match ($option) {
                    'auto' => 'Tự động ẩn',
                    'expanded' => 'Luôn mở',
                    'collapsed' => 'Thu gọn',
                    'locked' => 'Khóa cố định',
                }) }}
            </button>
        @endforeach
    </div>
</div>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sidebarModeManager', ({ initialMode }) => ({
                mode: initialMode ?? 'auto',
                init() {
                    const stored = window.localStorage.getItem('adminSidebarMode');
                    if (stored && ['auto','expanded','collapsed','locked'].includes(stored)) {
                        this.mode = stored;
                    }

                    this.applyMode(this.mode);

                    window.addEventListener('admin-sidebar-mode-updated', (event) => {
                        if (! event.detail?.mode) {
                            return;
                        }

                        this.mode = event.detail.mode;
                        this.applyMode(this.mode);
                    });

                    window.addEventListener('storage', (event) => {
                        if (event.key !== 'adminSidebarMode') {
                            return;
                        }

                        const next = event.newValue;
                        if (next && ['auto','expanded','collapsed','locked'].includes(next)) {
                            this.mode = next;
                            this.applyMode(this.mode);
                            this.$wire.setMode(this.mode);
                        }
                    });
                },
                changeMode(mode) {
                    if (! ['auto','expanded','collapsed','locked'].includes(mode)) {
                        return;
                    }

                    this.mode = mode;
                    this.applyMode(mode);
                    this.$wire.setMode(mode);
                },
                applyMode(mode) {
                    document.documentElement.dataset.adminSidebarMode = mode;
                    window.localStorage.setItem('adminSidebarMode', mode);
                },
            }));
        });
    </script>
@endonce
