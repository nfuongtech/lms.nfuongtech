const MODE_CLASSES = {
    auto: 'fi-sidebar-mode-auto',
    hidden: 'fi-sidebar-mode-hidden',
    pinned: 'fi-sidebar-mode-pinned',
    hover: 'fi-sidebar-mode-hover',
};

const SELECTORS = {
    layout: ['.fi-layout', '[data-panel-layout]', '[data-filament-panel-layout]'],
    sidebar: ['.fi-sidebar', '[data-panel-sidebar]', 'aside.fi-sidebar', 'aside[role="navigation"]'],
    main: ['.fi-main', '[data-panel-main]', 'main.fi-main', 'main[data-panel-main]'],
};

let layoutElement = null;
let sidebarElement = null;
let mainElement = null;
let hoverZoneElement = null;
let originalSidebarStyles = null;
let originalMainStyles = null;
let measuredSidebarWidth = null;
let currentMode = null;
let pendingMode = null;
let hoverActive = false;
let initialized = false;

const hoverEnterHandler = () => {
    if (currentMode === 'hover') {
        setHoverActive(true);
    }
};

const hoverLeaveHandler = (event) => {
    if (currentMode !== 'hover') {
        return;
    }

    if (event && sidebarElement && sidebarElement.contains(event.relatedTarget)) {
        return;
    }

    setHoverActive(false);
};

const hoverZoneLeaveHandler = () => {
    if (currentMode === 'hover') {
        setHoverActive(false);
    }
};

function getSidebarStore() {
    if (typeof window === 'undefined') {
        return null;
    }

    if (!window.Alpine || typeof window.Alpine.store !== 'function') {
        return null;
    }

    try {
        return window.Alpine.store('sidebar');
    } catch (error) {
        return null;
    }
}

function findElement(selectors, filter) {
    for (const selector of selectors) {
        const element = document.querySelector(selector);
        if (element && (!filter || filter(element))) {
            return element;
        }
    }

    return null;
}

function detachHoverListeners() {
    if (hoverZoneElement) {
        hoverZoneElement.removeEventListener('mouseenter', hoverEnterHandler);
        hoverZoneElement.removeEventListener('mouseleave', hoverZoneLeaveHandler);
    }

    if (sidebarElement) {
        sidebarElement.removeEventListener('mouseenter', hoverEnterHandler);
        sidebarElement.removeEventListener('mouseleave', hoverLeaveHandler);
    }
}

function ensureHoverZone() {
    if (!document.body) {
        return;
    }

    const width = Math.min(getSidebarWidth(), 48);

    if (!hoverZoneElement || !document.body.contains(hoverZoneElement)) {
        hoverZoneElement = document.createElement('div');
        hoverZoneElement.classList.add('fi-sidebar-hover-zone');
        document.body.appendChild(hoverZoneElement);
    }

    hoverZoneElement.style.width = `${width}px`;

    detachHoverListeners();

    hoverZoneElement.addEventListener('mouseenter', hoverEnterHandler);
    hoverZoneElement.addEventListener('mouseleave', hoverZoneLeaveHandler);

    if (sidebarElement) {
        sidebarElement.addEventListener('mouseenter', hoverEnterHandler);
        sidebarElement.addEventListener('mouseleave', hoverLeaveHandler);
    }
}

function updateElements() {
    if (!document.body || !document.body.classList.contains('fi-body')) {
        return;
    }

    const nextLayout = findElement(SELECTORS.layout);
    if (nextLayout) {
        layoutElement = nextLayout;
    }

    const nextSidebar = findElement(SELECTORS.sidebar);
    if (nextSidebar && nextSidebar !== sidebarElement) {
        detachHoverListeners();
        sidebarElement = nextSidebar;
        originalSidebarStyles = null;
        measuredSidebarWidth = null;
    }

    const nextMain = findElement(SELECTORS.main, (candidate) => {
        if (!sidebarElement) {
            return true;
        }

        return !sidebarElement.contains(candidate);
    });

    if (nextMain && nextMain !== sidebarElement) {
        mainElement = nextMain;
        originalMainStyles = null;
    }
}

function storeOriginalStyles() {
    if (sidebarElement && !originalSidebarStyles) {
        const computedSidebar = window.getComputedStyle(sidebarElement);
        originalSidebarStyles = {
            width: sidebarElement.style.width || '',
            minWidth: sidebarElement.style.minWidth || '',
            maxWidth: sidebarElement.style.maxWidth || '',
            flexBasis: sidebarElement.style.flexBasis || '',
            flexGrow: sidebarElement.style.flexGrow || '',
            flexShrink: sidebarElement.style.flexShrink || '',
            transform: sidebarElement.style.transform || '',
            opacity: sidebarElement.style.opacity || '',
            pointerEvents: sidebarElement.style.pointerEvents || '',
        };

        const computedWidth = parseFloat(computedSidebar.width);
        if (!Number.isNaN(computedWidth) && computedWidth > 0) {
            measuredSidebarWidth = computedWidth;
        }
    }

    if (mainElement && !originalMainStyles) {
        originalMainStyles = {
            marginLeft: mainElement.style.marginLeft || '',
            marginRight: mainElement.style.marginRight || '',
            width: mainElement.style.width || '',
            flexBasis: mainElement.style.flexBasis || '',
            flexGrow: mainElement.style.flexGrow || '',
            flexShrink: mainElement.style.flexShrink || '',
        };
    }
}

function applyInlineStyles(element, styles) {
    if (!element || !styles) {
        return;
    }

    Object.entries(styles).forEach(([property, value]) => {
        element.style[property] = value ?? '';
    });
}

function getSidebarWidth() {
    if (sidebarElement) {
        const rect = sidebarElement.getBoundingClientRect();
        if (rect.width > 0) {
            measuredSidebarWidth = rect.width;
            return rect.width;
        }
    }

    if (measuredSidebarWidth && measuredSidebarWidth > 0) {
        return measuredSidebarWidth;
    }

    if (sidebarElement) {
        const computedWidth = parseFloat(window.getComputedStyle(sidebarElement).width);
        if (!Number.isNaN(computedWidth) && computedWidth > 0) {
            measuredSidebarWidth = computedWidth;
            return computedWidth;
        }
    }

    return 288;
}

function collapseSidebar() {
    if (!sidebarElement) {
        return;
    }

    storeOriginalStyles();

    sidebarElement.style.flexBasis = '0px';
    sidebarElement.style.flexGrow = '0';
    sidebarElement.style.flexShrink = '0';
    sidebarElement.style.width = '0px';
    sidebarElement.style.minWidth = '0px';
    sidebarElement.style.maxWidth = '0px';
    sidebarElement.style.transform = 'translateX(-100%)';
    sidebarElement.style.opacity = '0';
    sidebarElement.style.pointerEvents = 'none';
}

function expandSidebar() {
    if (!sidebarElement) {
        return;
    }

    storeOriginalStyles();
    applyInlineStyles(sidebarElement, originalSidebarStyles);
}

function fillMain() {
    if (!mainElement) {
        return;
    }

    storeOriginalStyles();
    mainElement.style.marginLeft = '0';
    mainElement.style.marginRight = '0';
    mainElement.style.width = '100%';
    mainElement.style.flexBasis = 'auto';
    mainElement.style.flexGrow = '1';
    mainElement.style.flexShrink = '1';
}

function restoreMain() {
    if (!mainElement) {
        return;
    }

    storeOriginalStyles();
    applyInlineStyles(mainElement, originalMainStyles);
}

function setHoverActive(active) {
    if (currentMode !== 'hover') {
        hoverActive = false;
        return;
    }

    if (hoverActive === active) {
        return;
    }

    hoverActive = active;

    document.body.classList.toggle('fi-sidebar-hovering', hoverActive);

    const store = getSidebarStore();

    if (hoverActive) {
        expandSidebar();
        restoreMain();
        store?.open?.();
    } else {
        collapseSidebar();
        fillMain();
        store?.close?.();
    }
}

function updateBodyModeClass(mode) {
    Object.values(MODE_CLASSES).forEach((className) => {
        if (className) {
            document.body.classList.remove(className);
        }
    });

    const targetClass = MODE_CLASSES[mode];
    if (targetClass) {
        document.body.classList.add(targetClass);
    }
}

function applyMode(mode, { force = false } = {}) {
    if (!force && mode === currentMode) {
        return;
    }

    currentMode = mode;

    if (!document.body || !document.body.classList.contains('fi-body')) {
        pendingMode = mode;
        return;
    }

    if (!initialized || !sidebarElement || !mainElement) {
        pendingMode = mode;
        scheduleInitialization();
        return;
    }

    updateBodyModeClass(mode);

    const store = getSidebarStore();

    if (mode === 'hidden') {
        setHoverActive(false);
        collapseSidebar();
        fillMain();
        hoverZoneElement?.classList.remove('is-active');
        store?.close?.();
    } else if (mode === 'hover') {
        ensureHoverZone();
        hoverZoneElement?.classList.add('is-active');
        setHoverActive(false);
        collapseSidebar();
        fillMain();
        store?.close?.();
    } else if (mode === 'pinned') {
        hoverZoneElement?.classList.remove('is-active');
        setHoverActive(false);
        expandSidebar();
        restoreMain();
        store?.open?.();
    } else {
        hoverZoneElement?.classList.remove('is-active');
        setHoverActive(false);
        expandSidebar();
        restoreMain();
    }

    document.body.dataset.sidebarMode = mode;
    window.dispatchEvent(new CustomEvent('fi-sidebar-mode-updated', { detail: { mode } }));
}

function initialize() {
    if (initialized) {
        updateElements();
    } else {
        updateElements();
    }

    if (!sidebarElement || !mainElement) {
        return;
    }

    storeOriginalStyles();
    ensureHoverZone();

    initialized = true;

    if (pendingMode) {
        const modeToApply = pendingMode;
        pendingMode = null;
        applyMode(modeToApply, { force: true });
    } else if (currentMode) {
        applyMode(currentMode, { force: true });
    }
}

function scheduleInitialization() {
    if (!document.body) {
        return;
    }

    if (document.body.classList.contains('fi-body')) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => initialize(), { once: true });
        } else {
            requestAnimationFrame(() => initialize());
        }
    }
}

scheduleInitialization();

window.addEventListener('resize', () => {
    measuredSidebarWidth = null;
    if (hoverZoneElement && document.body.contains(hoverZoneElement)) {
        hoverZoneElement.style.width = `${Math.min(getSidebarWidth(), 48)}px`;
    }
});

document.addEventListener('livewire:navigated', () => {
    detachHoverListeners();

    if (hoverZoneElement && hoverZoneElement.parentElement) {
        hoverZoneElement.parentElement.removeChild(hoverZoneElement);
    }

    hoverZoneElement = null;
    layoutElement = null;
    sidebarElement = null;
    mainElement = null;
    originalSidebarStyles = null;
    originalMainStyles = null;
    measuredSidebarWidth = null;
    initialized = false;

    scheduleInitialization();
});

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;
    const sidebarStore = getSidebarStore();

    if (!sidebarStore) {
        return;
    }

    if (typeof sidebarStore.displayMode === 'undefined') {
        sidebarStore.displayMode = Alpine.$persist('auto').as('fiSidebarDisplayMode');
    }

    if (typeof sidebarStore.setDisplayMode !== 'function') {
        sidebarStore.setDisplayMode = function setDisplayMode(mode) {
            this.displayMode = mode;
        };
    }

    Alpine.effect(() => {
        const mode = sidebarStore.displayMode ?? 'auto';
        applyMode(mode);
    });

    window.addEventListener('fi-sidebar-set-mode', (event) => {
        const requestedMode = event.detail?.mode ?? 'auto';
        sidebarStore.setDisplayMode(requestedMode);
    });

    Alpine.data('sidebarModeDropdown', () => ({
        open: false,
        modeValue: sidebarStore.displayMode ?? 'auto',
        options: [
            { value: 'auto', label: 'Tự động ẩn' },
            { value: 'hidden', label: 'Ẩn' },
            { value: 'pinned', label: 'Luôn hiện' },
            { value: 'hover', label: 'Hiện khi di chuột' },
        ],
        get mode() {
            return this.modeValue ?? 'auto';
        },
        get currentLabel() {
            const active = this.options.find((option) => option.value === this.mode);
            return active ? active.label : '';
        },
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        select(mode) {
            this.modeValue = mode;
            window.dispatchEvent(new CustomEvent('fi-sidebar-set-mode', { detail: { mode } }));
            this.close();
        },
        sync(mode) {
            this.modeValue = mode ?? 'auto';
        },
        init() {
            this.sync(sidebarStore.displayMode ?? 'auto');
            window.addEventListener('fi-sidebar-mode-updated', (event) => {
                if (event.detail?.mode) {
                    this.sync(event.detail.mode);
                }
            });
        },
    }));
});
