@php
    $defaultSidebarMode = $sidebarMode ?? 'auto';
@endphp
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.documentElement;
        const storageKey = 'filament:admin:sidebar-mode';
        const validModes = ['auto', 'expanded', 'collapsed', 'locked'];
        const defaultMode = @json($defaultSidebarMode);

        const whenLivewireReady = (callback) => {
            if (window.Livewire?.dispatch) {
                callback();
                return;
            }

            window.addEventListener('livewire:load', callback, { once: true });
        };

        const notifyLivewire = (mode) => {
            whenLivewireReady(() => window.Livewire.dispatch('sidebar-mode-sync', { mode }));
        };

        const applyMode = (mode) => {
            if (!validModes.includes(mode)) {
                mode = defaultMode;
            }

            root.setAttribute('data-sidebar-mode', mode);

            document.querySelectorAll('[data-sidebar-mode-button]').forEach((button) => {
                button.classList.toggle('fi-sidebar-preferences__button--active', button.dataset.sidebarModeButton === mode);
            });

            notifyLivewire(mode);
        };

        const savedMode = localStorage.getItem(storageKey) || defaultMode;
        applyMode(savedMode);

        window.addEventListener('sidebar-mode-changed', (event) => {
            const mode = event.detail?.mode ?? defaultMode;
            localStorage.setItem(storageKey, mode);
            applyMode(mode);
        });

        window.addEventListener('storage', (event) => {
            if (event.key === storageKey && event.newValue) {
                applyMode(event.newValue);
            }
        });

    });
</script>
