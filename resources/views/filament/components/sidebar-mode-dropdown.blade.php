<div
    x-data="sidebarModeDropdown"
    x-cloak
    class="fi-sidebar-mode-dropdown hidden items-center gap-2 whitespace-nowrap sm:flex"
>
    <button
        type="button"
        class="fi-sidebar-mode-dropdown__button"
        x-on:click="toggle"
        x-bind:aria-expanded="open"
        aria-haspopup="true"
    >
        <svg class="fi-sidebar-mode-dropdown__button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M4 6.75h16M4 12h16M4 17.25h10" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="fi-sidebar-mode-dropdown__label" x-text="currentLabel"></span>
        <svg class="fi-sidebar-mode-dropdown__caret" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M5 7l5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition.opacity.duration.150ms
        x-on:click.outside="close"
        x-on:keydown.escape.window="close"
        class="fi-sidebar-mode-dropdown__menu"
    >
        <template x-for="option in options" :key="option.value">
            <button
                type="button"
                class="fi-sidebar-mode-dropdown__option"
                x-on:click="select(option.value)"
                :class="{ 'fi-sidebar-mode-dropdown__option--active': option.value === mode }"
            >
                <span x-text="option.label"></span>
                <svg
                    x-show="option.value === mode"
                    class="fi-sidebar-mode-dropdown__check"
                    viewBox="0 0 20 20"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <path d="M5 10.5l3 3 7-7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </template>
    </div>
</div>
