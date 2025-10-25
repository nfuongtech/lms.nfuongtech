@php
    $defaultSidebarMode = $sidebarMode ?? 'pinned';
@endphp
<script>
    (() => {
        const root = document.documentElement;
        const storageKey = 'filament:admin:sidebar-mode';
        const openStorageKey = 'filament:admin:sidebar-open';
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

        const parseOpenValue = (value) => {
            if (value === null || value === undefined) {
                return true;
            }

            return value !== 'false';
        };

        const updateModeButtons = (mode) => {
            document.querySelectorAll('[data-sidebar-mode-button]').forEach((button) => {
                button.classList.toggle(
                    'fi-sidebar-preferences__button--active',
                    button.dataset.sidebarModeButton === mode,
                );
            });
        };

        const updateToggleButtons = (isOpen) => {
            document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
                const openLabel = button.dataset.openLabel;
                const closeLabel = button.dataset.closeLabel;
                const label = isOpen ? closeLabel : openLabel;

                if (label) {
                    button.setAttribute('aria-label', label);
                    button.setAttribute('title', label);
                }

                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                button.dataset.sidebarToggleState = isOpen ? 'open' : 'closed';
            });
        };

        const applyOpenState = (open, { persist = true } = {}) => {
            const openValue = open ? 'true' : 'false';

            root.setAttribute('data-sidebar-open', openValue);
            updateToggleButtons(open);

            if (persist) {
                localStorage.setItem(openStorageKey, openValue);
            }
        };

        const getStoredOpenState = () => parseOpenValue(localStorage.getItem(openStorageKey));

        const applyMode = (rawMode) => {
            const mode = normalizeMode(rawMode);

            root.setAttribute('data-sidebar-mode', mode);

            if (mode === 'pinned') {
                applyOpenState(getStoredOpenState(), { persist: true });
            } else {
                applyOpenState(false, { persist: false });
            }

            updateModeButtons(mode);
            notifyLivewire(mode);

            return mode;
        };

        const toggleOpenState = () => {
            const isPinned = root.getAttribute('data-sidebar-mode') === 'pinned';
            const currentlyOpen = root.getAttribute('data-sidebar-open') !== 'false';
            const nextOpen = !currentlyOpen;

            applyOpenState(nextOpen, { persist: isPinned });
        };

        const handleToggleClick = (event) => {
            const toggle = event.target.closest('[data-sidebar-toggle]');

            if (! toggle) {
                return;
            }

            event.preventDefault();
            toggleOpenState();
        };

        const handleKeyDown = (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            const isOpen = root.getAttribute('data-sidebar-open') !== 'false';

            if (! isOpen) {
                return;
            }

            applyOpenState(false, { persist: root.getAttribute('data-sidebar-mode') === 'pinned' });
        };

        document.addEventListener('click', handleToggleClick);
        document.addEventListener('keydown', handleKeyDown);

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

            if (event.key === openStorageKey) {
                const isPinned = root.getAttribute('data-sidebar-mode') === 'pinned';

                if (! isPinned) {
                    return;
                }

                applyOpenState(parseOpenValue(event.newValue), { persist: false });
            }
        });
    })();
</script>
