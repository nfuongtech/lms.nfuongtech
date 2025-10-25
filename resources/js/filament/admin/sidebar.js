const STORAGE_KEY = 'filament:sidebar-mode';
const MODES = Object.freeze({
    AUTO: 'auto',
    HIDDEN: 'hidden',
    VISIBLE: 'visible',
    HOVER: 'hover',
});

const SELECTOR_SIDEBAR = 'aside.fi-sidebar';
const SELECTOR_LAYOUT = '.fi-layout';
const SELECTOR_MAIN = '.fi-main';
const HOVER_REGION_ID = 'sidebar-hover-region';

let currentMode = null;
let listenersAttached = false;

function readStoredMode() {
    try {
        return window.localStorage.getItem(STORAGE_KEY);
    } catch (error) {
        return null;
    }
}

function writeStoredMode(mode) {
    try {
        window.localStorage.setItem(STORAGE_KEY, mode);
    } catch (error) {
        // Ignore storage errors (e.g. Safari private mode)
    }
}

function onReady(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
}

function getElements() {
    const layout = document.querySelector(SELECTOR_LAYOUT);
    const sidebar = document.querySelector(SELECTOR_SIDEBAR);
    const main = document.querySelector(SELECTOR_MAIN);

    return { layout, sidebar, main };
}

function ensureHoverRegion() {
    let region = document.getElementById(HOVER_REGION_ID);

    if (!region) {
        region = document.createElement('div');
        region.id = HOVER_REGION_ID;
        region.dataset.sidebarHoverRegion = '';
        document.body.appendChild(region);
    }

    if (!region.dataset.listenerAttached) {
        region.addEventListener('pointerenter', () => {
            if (!document.body.classList.contains('sidebar-hover-enabled')) {
                return;
            }

            document.body.classList.add('sidebar-force-open');
        });

        region.addEventListener('pointerleave', () => {
            if (!document.body.classList.contains('sidebar-hover-enabled')) {
                return;
            }

            document.body.classList.remove('sidebar-force-open');
        });

        region.dataset.listenerAttached = 'true';
    }

    return region;
}

function bindSidebarHover(sidebar) {
    if (!sidebar || sidebar.dataset.hoverBound === 'true') {
        return;
    }

    sidebar.addEventListener('pointerenter', () => {
        if (document.body.classList.contains('sidebar-hover-enabled')) {
            document.body.classList.add('sidebar-force-open');
        }
    });

    sidebar.addEventListener('pointerleave', (event) => {
        if (!document.body.classList.contains('sidebar-hover-enabled')) {
            return;
        }

        if (event.relatedTarget && sidebar.contains(event.relatedTarget)) {
            return;
        }

        document.body.classList.remove('sidebar-force-open');
    });

    sidebar.dataset.hoverBound = 'true';
}

function translateModeLabel(mode) {
    switch (mode) {
        case MODES.HIDDEN:
            return 'Ẩn hoàn toàn';
        case MODES.VISIBLE:
            return 'Luôn hiển thị';
        case MODES.HOVER:
            return 'Hiện khi rê chuột';
        case MODES.AUTO:
        default:
            return 'Tự động ẩn';
    }
}

function updateLabel(mode) {
    document.querySelectorAll('[data-sidebar-mode-label]').forEach((label) => {
        label.textContent = `Đang: ${translateModeLabel(mode)}`;
    });
}

function syncSelect(mode) {
    document.querySelectorAll('[data-sidebar-mode-select]').forEach((select) => {
        if (select.value !== mode) {
            select.value = mode;
        }
    });
}

function shouldCollapse(mode) {
    if (mode === MODES.VISIBLE) {
        return false;
    }

    if (mode === MODES.HIDDEN || mode === MODES.HOVER) {
        return true;
    }

    const largeScreen = window.matchMedia('(min-width: 1280px)').matches;

    return !largeScreen;
}

function applyMode(mode) {
    const { sidebar, layout, main } = getElements();

    if (!sidebar || !layout || !main) {
        return;
    }

    const width = sidebar.getBoundingClientRect().width;

    if (width > 0) {
        document.documentElement.style.setProperty('--filament-sidebar-width', `${width}px`);
    }

    const collapse = shouldCollapse(mode);
    const enableHover = collapse && (mode === MODES.HOVER || (mode === MODES.AUTO && shouldCollapse(MODES.AUTO)));

    document.body.dataset.sidebarMode = mode;
    document.body.classList.toggle('sidebar-collapsed', collapse);
    document.body.classList.toggle('sidebar-hover-enabled', enableHover);

    if (!enableHover) {
        document.body.classList.remove('sidebar-force-open');
    }

    if (collapse) {
        layout.style.setProperty('--sidebar-width', '0px');
        layout.style.paddingInlineStart = 'clamp(1rem, 3vw, 1.5rem)';
        main.style.marginInlineStart = '0';
        main.style.paddingInlineStart = '0';
    } else {
        if (width > 0) {
            layout.style.setProperty('--sidebar-width', `${width}px`);
        } else {
            layout.style.removeProperty('--sidebar-width');
        }
        layout.style.removeProperty('padding-inline-start');
        main.style.removeProperty('margin-inline-start');
        main.style.removeProperty('padding-inline-start');
    }

    currentMode = mode;
    updateLabel(mode);
    syncSelect(mode);
}

function handleSelectChange(event) {
    if (!(event.target instanceof HTMLSelectElement)) {
        return;
    }

    if (!event.target.matches('[data-sidebar-mode-select]')) {
        return;
    }

    const mode = event.target.value;

    if (!Object.values(MODES).includes(mode)) {
        return;
    }

    writeStoredMode(mode);
    applyMode(mode);
}

function handleResize() {
    if (currentMode !== MODES.AUTO) {
        return;
    }

    applyMode(currentMode);
}

function attachListeners() {
    if (listenersAttached) {
        return;
    }

    document.addEventListener('change', handleSelectChange);
    window.addEventListener('resize', handleResize);

    listenersAttached = true;
}

function init() {
    const { sidebar, layout } = getElements();

    if (!sidebar || !layout) {
        return;
    }

    ensureHoverRegion();
    bindSidebarHover(sidebar);

    const storedMode = readStoredMode();
    const mode = Object.values(MODES).includes(storedMode) ? storedMode : MODES.AUTO;

    applyMode(mode);
    document.body.dataset.sidebarReady = 'true';

    attachListeners();
}

onReady(init);

window.addEventListener('livewire:navigated', () => {
    requestAnimationFrame(() => init());
});
