import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Prompts the user before leaving the page unless an intentional action
 * (e.g. "Continue anyway", "Post request", "Cancel") has been clicked.
 * Used on the pre-post pages so an unposted sauce request is not abandoned
 * silently.
 */
Alpine.data('leavePrompt', () => ({
    leaving: false,

    init() {
        window.addEventListener('beforeunload', (event) => {
            if (this.leaving) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });
    },

    allowLeave() {
        this.leaving = true;
    },
}));

/**
 * First-visit explicit-content warning dialog.
 *
 * Shown once to guests who haven't chosen a preference yet, asking whether
 * they want to hide explicit content. The choice is stored in a `hide_nsfw`
 * cookie (which the feed already reads) so it never re-prompts.
 *
 * The layout only renders this component for guests without a `hide_nsfw`
 * cookie, so the dialog is always shown when the component mounts.
 */
Alpine.data('explicitContentDialog', () => ({
    show: true,

    /**
     * Persist the visitor's choice and close the dialog.
     */
    choose(hide) {
        document.cookie = `hide_nsfw=${hide ? 1 : 0}; path=/; max-age=31536000; SameSite=Lax`;
        this.show = false;
    },

    /**
     * Close without choosing. A `hide_nsfw=0` cookie is set so the dialog
     * does not reappear on every page load.
     */
    dismiss() {
        document.cookie = 'hide_nsfw=0; path=/; max-age=31536000; SameSite=Lax';
        this.show = false;
    },
}));

Alpine.start();
