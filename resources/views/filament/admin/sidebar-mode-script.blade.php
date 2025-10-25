<style>
    :root[data-sidebar-mode] .fi-sidebar {
        transition: transform 220ms ease, opacity 220ms ease;
    }

    :root[data-sidebar-mode] .fi-main,
    :root[data-sidebar-mode] main.fi-main {
        transition: margin 220ms ease, padding 220ms ease;
    }

    :root[data-sidebar-mode="hidden"] .fi-sidebar,
    :root[data-sidebar-mode="auto"]:not(.fi-sidebar-open) .fi-sidebar {
        transform: translateX(-100%);
        opacity: 0;
        pointer-events: none;
    }

    :root[data-sidebar-mode="auto"].fi-sidebar-open .fi-sidebar,
    :root[data-sidebar-mode="visible"] .fi-sidebar {
        transform: translateX(0);
        opacity: 1;
        pointer-events: auto;
    }

    :root[data-sidebar-mode="hidden"] .fi-layout,
    :root[data-sidebar-mode="auto"] .fi-layout {
        grid-template-columns: 0 1fr !important;
    }

    :root[data-sidebar-mode="hidden"] .fi-main,
    :root[data-sidebar-mode="auto"] .fi-main {
        margin-left: 0 !important;
        padding-left: 0 !important;
        width: 100% !important;
    }

    .fi-main.fi-main-expanded {
        margin-left: 0 !important;
        padding-left: 0 !important;
        width: 100% !important;
    }

    .fi-sidebar-hover-zone {
        position: fixed;
        inset: 0 auto 0 0;
        width: 1.25rem;
        z-index: 60;
        pointer-events: none;
        background: transparent;
    }

    :root[data-sidebar-mode="auto"] .fi-sidebar-hover-zone {
        pointer-events: auto;
    }

    :root:not([data-sidebar-mode="auto"]) .fi-sidebar-hover-zone {
        display: none;
    }

    @media (max-width: 1023px) {
        .fi-sidebar-mode-toggle {
            display: none !important;
        }
    }
</style>

<script>
    (() => {
        const MODE_STORAGE_KEY = 'filamentAdminSidebarMode';
        const ALLOWED_MODES = ['auto', 'hidden', 'visible'];
        const root = document.documentElement;
        let hoverZone = null;
        let sidebar = null;
        let main = null;
        let select = null;
        let closeTimeout = null;

        const storedOnBoot = (() => {
            try {
                return localStorage.getItem(MODE_STORAGE_KEY);
            } catch (error) {
                console.warn('Không thể đọc chế độ sidebar khi khởi tạo:', error);
                return null;
            }
        })();

        if (storedOnBoot && ALLOWED_MODES.includes(storedOnBoot)) {
            root.dataset.sidebarMode = storedOnBoot;
        } else if (!root.dataset.sidebarMode) {
            root.dataset.sidebarMode = 'auto';
        }

        const findSidebar = () =>
            document.querySelector('[data-panel-sidebar]') ?? document.querySelector('.fi-sidebar');

        const findMain = () =>
            document.querySelector('[data-panel-main]') ?? document.querySelector('.fi-main');

        const findSelect = () =>
            document.querySelector('[data-sidebar-mode-select]');

        const ensureHoverZone = () => {
            if (!hoverZone) {
                hoverZone = document.createElement('div');
                hoverZone.className = 'fi-sidebar-hover-zone';
                hoverZone.setAttribute('aria-hidden', 'true');
                document.body.appendChild(hoverZone);
            }
            return hoverZone;
        };

        const setSidebarOpen = (isOpen) => {
            root.classList.toggle('fi-sidebar-open', isOpen);
        };

        const adjustMainSpacing = (mode) => {
            main = findMain();
            if (!main) {
                return;
            }

            if (mode === 'visible') {
                main.classList.remove('fi-main-expanded');
                main.style.removeProperty('margin-left');
                main.style.removeProperty('padding-left');
            } else {
                main.classList.add('fi-main-expanded');
                main.style.marginLeft = '0px';
                main.style.paddingLeft = '0px';
            }
        };

        const applyMode = (mode, { save = true } = {}) => {
            if (!ALLOWED_MODES.includes(mode)) {
                mode = 'auto';
            }

            if (save) {
                try {
                    localStorage.setItem(MODE_STORAGE_KEY, mode);
                } catch (error) {
                    console.warn('Không thể lưu chế độ sidebar:', error);
                }
            }

            root.dataset.sidebarMode = mode;
            adjustMainSpacing(mode);
            ensureHoverZone();

            if (mode === 'visible') {
                setSidebarOpen(true);
            } else if (mode === 'hidden') {
                setSidebarOpen(false);
            } else {
                setSidebarOpen(false);
            }
        };

        const handleSelectChange = (event) => {
            applyMode(event.target.value);
        };

        const openForAutoMode = () => {
            if (root.dataset.sidebarMode !== 'auto') {
                return;
            }

            clearTimeout(closeTimeout);
            setSidebarOpen(true);
        };

        const closeForAutoMode = () => {
            if (root.dataset.sidebarMode !== 'auto') {
                return;
            }

            clearTimeout(closeTimeout);
            closeTimeout = window.setTimeout(() => setSidebarOpen(false), 180);
        };

        const bindHoverListeners = () => {
            if (!hoverZone || !sidebar) {
                return;
            }

            hoverZone.addEventListener('mouseenter', openForAutoMode);
            hoverZone.addEventListener('mouseleave', closeForAutoMode);
            sidebar.addEventListener('mouseenter', openForAutoMode);
            sidebar.addEventListener('mouseleave', closeForAutoMode);
        };

        const unbindHoverListeners = () => {
            if (hoverZone) {
                hoverZone.removeEventListener('mouseenter', openForAutoMode);
                hoverZone.removeEventListener('mouseleave', closeForAutoMode);
            }

            if (sidebar) {
                sidebar.removeEventListener('mouseenter', openForAutoMode);
                sidebar.removeEventListener('mouseleave', closeForAutoMode);
            }
        };

        const handleDocumentClick = (event) => {
            if (root.dataset.sidebarMode !== 'auto') {
                return;
            }

            if (!sidebar?.contains(event.target) && !select?.contains(event.target)) {
                setSidebarOpen(false);
            }
        };

        const handleEscape = (event) => {
            if (event.key === 'Escape' && root.dataset.sidebarMode === 'auto') {
                setSidebarOpen(false);
            }
        };

        const initialize = () => {
            sidebar = findSidebar();
            main = findMain();
            select = findSelect();

            if (!sidebar || !main || !select) {
                return;
            }

            unbindHoverListeners();
            ensureHoverZone();
            bindHoverListeners();

            select.removeEventListener('change', handleSelectChange);
            select.addEventListener('change', handleSelectChange);

            const storedMode = (() => {
                try {
                    return localStorage.getItem(MODE_STORAGE_KEY);
                } catch (error) {
                    console.warn('Không thể đọc chế độ sidebar đã lưu:', error);
                    return null;
                }
            })();

            const initialMode = storedMode && ALLOWED_MODES.includes(storedMode)
                ? storedMode
                : (select.value && ALLOWED_MODES.includes(select.value) ? select.value : 'auto');

            applyMode(initialMode, { save: false });
            select.value = initialMode;
        };

        document.addEventListener('click', handleDocumentClick);
        document.addEventListener('keydown', handleEscape);

        document.addEventListener('DOMContentLoaded', () => {
            initialize();
        });

        document.addEventListener('livewire:navigated', () => {
            window.setTimeout(initialize, 50);
        });
    })();
</script>
