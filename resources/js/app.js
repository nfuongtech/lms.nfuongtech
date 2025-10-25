import './bootstrap';

const SIDEBAR_MODE_KEY = 'filamentAdminSidebarMode';
const sidebarModeClasses = [
    'sidebar-mode-auto',
    'sidebar-mode-expanded',
    'sidebar-mode-collapsed',
    'sidebar-mode-hidden',
    'sidebar-mode-locked',
];

const applySidebarMode = (mode = 'auto') => {
    const body = document.body;

    if (!body) {
        return;
    }

    body.classList.add('sidebar-mode-enabled');
    body.classList.remove(...sidebarModeClasses);

    if (mode === 'auto') {
        body.classList.add('sidebar-mode-auto');
        const derived = window.innerWidth < 1280 ? 'collapsed' : 'expanded';
        body.classList.add(`sidebar-mode-${derived}`);
        return;
    }

    if (mode === 'locked') {
        body.classList.add('sidebar-mode-expanded');
        body.classList.add('sidebar-mode-locked');
        return;
    }

    body.classList.add(`sidebar-mode-${mode}`);
};

const initializeSidebarPreference = () => {
    const stored = window.localStorage.getItem(SIDEBAR_MODE_KEY) ?? 'auto';
    applySidebarMode(stored);

    window.addEventListener('resize', () => {
        if ((window.localStorage.getItem(SIDEBAR_MODE_KEY) ?? 'auto') === 'auto') {
            applySidebarMode('auto');
        }
    });
};

document.addEventListener('DOMContentLoaded', initializeSidebarPreference);

document.addEventListener('alpine:init', () => {
    window.Alpine.data('sidebarPreferencesComponent', () => ({
        mode: window.localStorage.getItem(SIDEBAR_MODE_KEY) ?? 'auto',
        init() {
            applySidebarMode(this.mode);
        },
        setMode(mode) {
            this.mode = mode;
            window.localStorage.setItem(SIDEBAR_MODE_KEY, mode);
            applySidebarMode(mode);
        },
        buttonClasses(target) {
            const active = this.mode === target || (this.mode === 'auto' && target === 'auto');

            return active
                ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-500/10 dark:text-primary-200'
                : 'border-gray-200 bg-white text-gray-600 hover:border-primary-200 hover:bg-primary-50 hover:text-primary-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-primary-400 dark:hover:bg-primary-500/10';
        },
    }));
});

window.addEventListener('storage', (event) => {
    if (event.key === SIDEBAR_MODE_KEY && event.newValue) {
        applySidebarMode(event.newValue);
    }
});

window.applySidebarMode = applySidebarMode;
