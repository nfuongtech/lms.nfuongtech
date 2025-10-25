@php
    $defaultSidebarMode = $sidebarMode ?? 'pinned';
@endphp
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.documentElement;
        const storageKey = 'filament:admin:sidebar-mode';
        const validModes = ['hidden', 'hover', 'pinned'];
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

        const normalizeMode = (mode) => (validModes.includes(mode) ? mode : 'pinned');

        const applyMode = (rawMode) => {
            const mode = normalizeMode(rawMode);

            root.setAttribute('data-sidebar-mode', mode);

            document.querySelectorAll('[data-sidebar-mode-button]').forEach((button) => {
                button.classList.toggle('fi-sidebar-preferences__button--active', button.dataset.sidebarModeButton === mode);
            });

            notifyLivewire(mode);
        };

        const savedMode = normalizeMode(localStorage.getItem(storageKey) || defaultMode);
        applyMode(savedMode);

        window.addEventListener('sidebar-mode-changed', (event) => {
            const mode = normalizeMode(event.detail?.mode ?? defaultMode);
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
