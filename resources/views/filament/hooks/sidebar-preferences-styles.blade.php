<style>
    .fi-sidebar-preferences__wrapper {
        padding: 1rem 1.25rem 1.5rem;
        border-bottom: 1px solid rgb(229 231 235 / 1);
    }

    .fi-sidebar-preferences {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .fi-sidebar-preferences__label {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgb(107 114 128 / 1);
    }

    .fi-sidebar-preferences__buttons {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .fi-sidebar-preferences__button {
        flex: 1 1 0;
        border-radius: 0.75rem;
        border: 1px solid rgb(229 231 235 / 1);
        background: rgb(249 250 251 / 1);
        color: rgb(55 65 81 / 1);
        font-size: 0.875rem;
        padding: 0.5rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease-in-out;
    }

    .fi-sidebar-preferences__button-icon {
        width: 1.5rem;
        height: 1.5rem;
    }

    .fi-sidebar-preferences__button-label {
        font-size: 0.75rem;
        font-weight: 600;
    }

    .fi-sidebar-preferences__button:hover,
    .fi-sidebar-preferences__button:focus {
        border-color: rgb(251 191 36 / 1);
        color: rgb(30 41 59 / 1);
    }

    .fi-sidebar-preferences__button--active {
        border-color: rgb(251 191 36 / 1);
        background: rgb(255 251 235 / 1);
        color: rgb(120 53 15 / 1);
        box-shadow: inset 0 0 0 1px rgb(251 191 36 / 0.25);
    }

    @media (min-width: 1024px) {
        :root[data-sidebar-mode="hidden"] .fi-sidebar {
            position: relative;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 30px rgb(15 23 42 / 0.08);
            z-index: 30;
        }

        :root[data-sidebar-mode="hidden"] .fi-layout:hover .fi-sidebar,
        :root[data-sidebar-mode="hidden"] .fi-sidebar:focus-within {
            transform: translateX(0);
        }

        :root[data-sidebar-mode="hidden"] .fi-main {
            margin-left: 0 !important;
            transition: margin 0.3s ease;
        }

        :root[data-sidebar-mode="hover"] .fi-sidebar {
            position: relative;
            transform: translateX(calc(-100% + 1.5rem));
            transition: transform 0.3s ease;
            box-shadow: 0 10px 30px rgb(15 23 42 / 0.08);
            z-index: 30;
        }

        :root[data-sidebar-mode="hover"] .fi-sidebar::after {
            content: '';
            position: absolute;
            top: 0;
            right: -1.5rem;
            width: 1.5rem;
            height: 100%;
            pointer-events: auto;
            z-index: 5;
        }

        :root[data-sidebar-mode="hover"] .fi-sidebar:hover,
        :root[data-sidebar-mode="hover"] .fi-sidebar:focus-within {
            transform: translateX(0);
        }

        :root[data-sidebar-mode="hover"] .fi-main {
            margin-left: 0 !important;
            transition: margin 0.3s ease;
        }

        :root[data-sidebar-mode="pinned"] .fi-sidebar {
            transform: translateX(0);
        }
    }
</style>
