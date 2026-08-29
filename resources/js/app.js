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

Alpine.start();
