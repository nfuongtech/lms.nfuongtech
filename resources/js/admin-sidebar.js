const SIDEBAR_MODES = {
    AUTO: 'auto',
    HIDDEN: 'hidden',
    PINNED: 'pinned',
    HOVER: 'hover',
};

const STORAGE_KEY = 'filamentSidebarMode';

function initializeSidebarEnhancements() {
    const layout = document.querySelector('.fi-layout');
    const sidebar = layout?.querySelector('.fi-sidebar');
    const main = layout?.querySelector('.fi-main') ?? layout?.querySelector('main');

    if (!layout || !sidebar || !main) {
        return;
    }

    if (layout.dataset.sidebarEnhanced === 'true') {
        return;
    }

    layout.dataset.sidebarEnhanced = 'true';
    layout.classList.add('fi-layout--sidebar-enhanced');
    document.body.dataset.sidebarEnhanced = 'true';

    // Avoid duplicating floating controls if the script somehow re-runs.
    if (!document.querySelector('.fi-sidebar-mode-control')) {
        const control = document.createElement('div');
        control.className = 'fi-sidebar-mode-control';
        control.innerHTML = `
            <label class="fi-sidebar-mode-label">
                <span class="fi-sidebar-mode-title">Sidebar</span>
                <span class="fi-sidebar-mode-select-wrapper">
                    <select class="fi-sidebar-mode-select" aria-label="Chế độ hiển thị sidebar">
                        <option value="${SIDEBAR_MODES.AUTO}">Tự động ẩn</option>
                        <option value="${SIDEBAR_MODES.HIDDEN}">Ẩn</option>
                        <option value="${SIDEBAR_MODES.PINNED}">Luôn hiện</option>
                        <option value="${SIDEBAR_MODES.HOVER}">Hiện khi di chuột</option>
                    </select>
                    <svg class="fi-sidebar-mode-select-indicator" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.195l3.71-3.965a.75.75 0 1 1 1.08 1.04l-4.25 4.54a.75.75 0 0 1-1.08 0l-4.25-4.54a.75.75 0 0 1 .02-1.06z"></path>
                    </svg>
                </span>
                <span class="fi-sidebar-mode-hint">Tùy chỉnh cách hiển thị thanh điều hướng</span>
            </label>
        `;

        document.body.appendChild(control);
    }

    const controlContainer = document.querySelector('.fi-sidebar-mode-control');
    const select = controlContainer.querySelector('select');

    const revealZone = document.querySelector('.fi-sidebar-reveal-zone') ?? (() => {
        const zone = document.createElement('div');
        zone.className = 'fi-sidebar-reveal-zone';
        document.body.appendChild(zone);

        return zone;
    })();

    let baseSidebarWidth = Math.round(sidebar.getBoundingClientRect().width) || 288;
    layout.style.setProperty('--sidebar-width', `${baseSidebarWidth}px`);

    let currentMode = select.value;
    const storedMode = localStorage.getItem(STORAGE_KEY);
    if (storedMode && Object.values(SIDEBAR_MODES).includes(storedMode)) {
        currentMode = storedMode;
    }
    select.value = currentMode;

    let isSidebarVisible = true;
    let autoHideTimeout = null;
    let hoverHideTimeout = null;

    const recalcSidebarWidth = () => {
        const measured = sidebar.getBoundingClientRect().width;
        if (measured > 0) {
            baseSidebarWidth = Math.round(measured);
            layout.style.setProperty('--sidebar-width', `${baseSidebarWidth}px`);
        }
    };

    const setBodyModeData = () => {
        document.body.dataset.sidebarMode = currentMode;
    };

    const setVisibility = (visible, { immediate = false } = {}) => {
        if (isSidebarVisible === visible && !immediate) {
            return;
        }

        isSidebarVisible = visible;
        layout.dataset.sidebarState = visible ? 'visible' : 'hidden';
        document.body.dataset.sidebarVisible = visible ? 'true' : 'false';

        if (visible) {
            recalcSidebarWidth();
        }

        if (!immediate) {
            layout.classList.add('fi-layout--sidebar-animating');
            window.setTimeout(() => {
                layout.classList.remove('fi-layout--sidebar-animating');
            }, 400);
        }
    };

    const cancelAutoHide = () => {
        if (autoHideTimeout) {
            window.clearTimeout(autoHideTimeout);
            autoHideTimeout = null;
        }
    };

    const scheduleAutoHide = () => {
        cancelAutoHide();

        if (currentMode !== SIDEBAR_MODES.AUTO) {
            return;
        }

        autoHideTimeout = window.setTimeout(() => {
            if (currentMode === SIDEBAR_MODES.AUTO) {
                setVisibility(false);
            }
        }, 2200);
    };

    const cancelHoverHide = () => {
        if (hoverHideTimeout) {
            window.clearTimeout(hoverHideTimeout);
            hoverHideTimeout = null;
        }
    };

    const scheduleHoverHide = () => {
        cancelHoverHide();

        if (currentMode !== SIDEBAR_MODES.HOVER) {
            return;
        }

        hoverHideTimeout = window.setTimeout(() => {
            if (currentMode === SIDEBAR_MODES.HOVER) {
                setVisibility(false);
            }
        }, 260);
    };

    const updateRevealZone = () => {
        if (currentMode === SIDEBAR_MODES.PINNED || currentMode === SIDEBAR_MODES.HIDDEN) {
            revealZone.style.pointerEvents = 'none';
        } else {
            revealZone.style.pointerEvents = 'auto';
        }
    };

    const applyMode = (mode, { persist = true, immediate = true } = {}) => {
        if (!Object.values(SIDEBAR_MODES).includes(mode)) {
            mode = SIDEBAR_MODES.AUTO;
        }

        currentMode = mode;

        if (persist) {
            localStorage.setItem(STORAGE_KEY, currentMode);
        }

        setBodyModeData();
        cancelAutoHide();
        cancelHoverHide();

        if (currentMode === SIDEBAR_MODES.PINNED) {
            setVisibility(true, { immediate });
        } else if (currentMode === SIDEBAR_MODES.HIDDEN) {
            setVisibility(false, { immediate });
        } else if (currentMode === SIDEBAR_MODES.HOVER) {
            setVisibility(false, { immediate });
        } else {
            setVisibility(true, { immediate });
            scheduleAutoHide();
        }

        updateRevealZone();
        select.value = currentMode;
    };

    select.addEventListener('change', (event) => {
        applyMode(event.target.value, { persist: true, immediate: false });
    });

    sidebar.addEventListener('mouseenter', () => {
        cancelAutoHide();
        cancelHoverHide();

        if (!isSidebarVisible) {
            setVisibility(true);
        }
    });

    sidebar.addEventListener('mouseleave', () => {
        if (currentMode === SIDEBAR_MODES.AUTO) {
            scheduleAutoHide();
        } else if (currentMode === SIDEBAR_MODES.HOVER) {
            scheduleHoverHide();
        }
    });

    revealZone.addEventListener('mouseenter', () => {
        if (currentMode === SIDEBAR_MODES.AUTO || currentMode === SIDEBAR_MODES.HOVER) {
            cancelAutoHide();
            cancelHoverHide();
            setVisibility(true);
        }
    });

    revealZone.addEventListener('mouseleave', () => {
        if (currentMode === SIDEBAR_MODES.AUTO) {
            scheduleAutoHide();
        } else if (currentMode === SIDEBAR_MODES.HOVER) {
            scheduleHoverHide();
        }
    });

    main.addEventListener('mouseenter', () => {
        if (currentMode === SIDEBAR_MODES.AUTO && isSidebarVisible) {
            scheduleAutoHide();
        } else if (currentMode === SIDEBAR_MODES.HOVER && isSidebarVisible) {
            scheduleHoverHide();
        }
    });

    main.addEventListener('focusin', () => {
        if (!isSidebarVisible && (currentMode === SIDEBAR_MODES.AUTO || currentMode === SIDEBAR_MODES.HOVER)) {
            setVisibility(true);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && (currentMode === SIDEBAR_MODES.AUTO || currentMode === SIDEBAR_MODES.HOVER)) {
            setVisibility(false);
        }
    });

    window.addEventListener('resize', () => {
        if (isSidebarVisible) {
            recalcSidebarWidth();
        }
    });

    if (window.ResizeObserver) {
        const resizeObserver = new ResizeObserver(() => {
            if (isSidebarVisible) {
                recalcSidebarWidth();
            }
        });

        resizeObserver.observe(sidebar);
    }

    requestAnimationFrame(() => {
        controlContainer.classList.add('fi-sidebar-mode-control--visible');
    });

    applyMode(currentMode, { persist: false, immediate: true });
    setBodyModeData();
}

function bootSidebarEnhancements() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSidebarEnhancements, { once: true });
    } else {
        initializeSidebarEnhancements();
    }
}

bootSidebarEnhancements();

document.addEventListener('livewire:navigated', () => {
    window.requestAnimationFrame(() => {
        initializeSidebarEnhancements();
    });
});
