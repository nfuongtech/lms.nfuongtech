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
        display: grid;
        gap: 0.5rem;
    }

    .fi-sidebar-preferences__button {
        width: 100%;
        border-radius: 0.75rem;
        border: 1px solid transparent;
        background: rgb(249 250 251 / 1);
        color: rgb(55 65 81 / 1);
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        text-align: left;
        transition: all 0.2s ease-in-out;
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

    .fi-sidebar-preferences__button-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    @media (min-width: 1024px) {
        :root[data-sidebar-mode="auto"] .fi-sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        :root[data-sidebar-mode="auto"] .fi-layout:hover .fi-sidebar,
        :root[data-sidebar-mode="auto"] .fi-sidebar:hover,
        :root[data-sidebar-mode="auto"] .fi-sidebar:focus-within {
            transform: translateX(0);
        }

        :root[data-sidebar-mode="auto"] .fi-main {
            margin-left: 0 !important;
        }

        :root[data-sidebar-mode="collapsed"] .fi-sidebar {
            width: 5rem;
            overflow: hidden;
        }

        :root[data-sidebar-mode="collapsed"] .fi-sidebar .fi-sidebar-item-label {
            opacity: 0;
            pointer-events: none;
        }

        :root[data-sidebar-mode="collapsed"] .fi-main {
            margin-left: 6rem !important;
            transition: margin 0.2s ease;
        }

        :root[data-sidebar-mode="expanded"] .fi-sidebar,
        :root[data-sidebar-mode="locked"] .fi-sidebar {
            transform: translateX(0);
        }

        :root[data-sidebar-mode="locked"] .fi-sidebar-toggle {
            display: none;
        }
    }
</style>
