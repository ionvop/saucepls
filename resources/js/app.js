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
 * Shown once to new visitors, asking whether they want to hide explicit
 * content. The choice is persisted so it never re-prompts:
 *  - Authenticated users: saved to their account setting (users.hide_nsfw)
 *    via the content-preference endpoint, syncing with the Settings toggle.
 *  - Guests: stored in a `hide_nsfw` cookie, which the feed already reads.
 *
 * The component is driven by data attributes set in the layout:
 *  - data-authed="1|0"  whether the visitor is logged in
 *  - data-hide-nsfw="1|0" the visitor's current hide-nsfw preference
 *  - data-cookie="1|0"  whether the guest already has a hide_nsfw cookie
 */
Alpine.data('explicitContentDialog', () => ({
    show: false,
    authed: false,
    hideNsfw: false,
    hasCookie: false,
    saving: false,

    init() {
        const el = this.$el;
        this.authed = el.dataset.authed === '1';
        this.hideNsfw = el.dataset.hideNsfw === '1';
        this.hasCookie = el.dataset.cookie === '1';

        // Already decided: authed users with a preference, or guests with a cookie.
        if (this.authed ? this.hideNsfw : this.hasCookie) {
            return;
        }

        this.show = true;
    },

    /**
     * Persist the visitor's choice and close the dialog.
     */
    async choose(hide) {
        if (this.saving) {
            return;
        }

        this.saving = true;

        if (this.authed) {
            try {
                await fetch(this.$el.dataset.endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ hide_nsfw: hide }),
                });
            } catch (e) {
                // Persistence is best-effort; still close so we don't nag.
            }
        } else {
            document.cookie = `hide_nsfw=${hide ? 1 : 0}; path=/; max-age=31536000; SameSite=Lax`;
        }

        this.saving = false;
        this.show = false;
    },

    /**
     * Close without choosing. Guests get a `hide_nsfw=0` cookie so the
     * dialog does not reappear on every page load.
     */
    dismiss() {
        if (!this.authed) {
            document.cookie = 'hide_nsfw=0; path=/; max-age=31536000; SameSite=Lax';
        }

        this.show = false;
    },
}));

Alpine.start();
