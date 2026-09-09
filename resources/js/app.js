import Alpine from 'alpinejs';
import './time';

window.Alpine = Alpine;

/**
 * Custom-styled dropdown used by `<x-select>`.
 * Replaces native `<select>` popups (which render white-on-white in dark mode
 * and can't be styled/inspected) with a DOM-rendered, styleable listbox.
 */
Alpine.data('dropdown', ({ name, value, options, selectedLabel }) => ({
    name,
    value,
    options,
    selectedLabel,
    open: false,

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },

    select(optionValue) {
        this.value = optionValue;
        this.selectedLabel = this.options[optionValue] ?? optionValue;
        this.close();
        this.$refs.button.focus();
    },

    moveFocus(direction) {
        const optionValues = Object.keys(this.options);
        let index = optionValues.indexOf(this.value);
        if (index === -1) {
            index = 0;
        }
        index = Math.min(Math.max(index + direction, 0), optionValues.length - 1);
        this.value = optionValues[index];
        this.selectedLabel = this.options[this.value];
    },
}));

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

/**
 * Client-side explicit-content toggle for guests on the settings page.
 *
 * Reads the current `hide_nsfw` cookie on mount and writes it back when the
 * user toggles the switch. Uses the same cookie format as the first-visit
 * dialog so the feed's guest filtering picks it up immediately.
 */

/**
 * Populates the search form's hidden `tz` input with the visitor's IANA
 * timezone so date prefixes (since:/until:) can be converted from the
 * visitor's timezone to UTC on the server. When JavaScript is disabled the
 * input stays empty and the server falls back to UTC.
 */
Alpine.data('searchTimezone', () => ({
    init() {
        try {
            const timeZone = new Intl.DateTimeFormat().resolvedOptions().timeZone;
            this.$refs.tz.value = timeZone;
        } catch {
            // Leave empty; the server defaults to UTC.
        }
    },
}));

/**
 * Reusable confirmation dialog.
 *
 * Replaces native `confirm()` calls with a styled Alpine modal. Call
 * `ask(message, action)` to open it with a message and the action to run
 * when the user confirms. The action is only invoked from the modal's
 * confirm button, never from the element that opened it.
 */
Alpine.data('confirmDialog', () => ({
    open: false,
    message: '',
    title: 'Delete',
    confirmAction: null,

    ask(message, title, action) {
        this.message = message;
        this.title = title;
        this.confirmAction = action;
        this.open = true;
    },

    confirm() {
        this.open = false;
        if (typeof this.confirmAction === 'function') {
            this.confirmAction();
        }
    },

    cancel() {
        this.open = false;
    },
}));

Alpine.data('guestNsfwToggle', () => ({
    hideNsfw: false,

    init() {
        this.hideNsfw = document.cookie
            .split('; ')
            .find((row) => row.startsWith('hide_nsfw='))
            ?.split('=')[1] === '1';
    },

    save() {
        document.cookie = `hide_nsfw=${this.hideNsfw ? 1 : 0}; path=/; max-age=31536000; SameSite=Lax`;
    },
}));

/**
 * Tag autocomplete for the search field on the search page.
 *
 * Watches the `q` input. A suggestion dropdown is shown only when:
 *   1. the text cursor is at the very end of the value, and
 *   2. the user has paused typing (debounce), and
 *   3. the last word of the query is at least two characters.
 * The last word is used as a prefix to fetch matching tags. Selecting a
 * suggestion replaces only that trailing word with the chosen tag name.
 */
Alpine.data('tagSuggestions', ({ endpoint }) => ({
    value: '',
    open: false,
    suggestions: [],
    highlightIndex: -1,
    debounceTimer: null,
    controller: null,

    init() {
        // Preserve the server-rendered query (from ?q=...) so the field
        // retains the search term on page load. Without this, Alpine's
        // x-model binding would overwrite the input value with the initial
        // empty string.
        this.value = this.$refs.input.value;

        // Re-run the last word/caret logic and debounced fetch on every
        // keystroke, including when no actual value change occurs.
        this.$watch('value', () => this.scheduleLookup());
    },

    /**
     * Extract the trailing word from the current query value.
     */
    currentWord() {
        const match = this.value.match(/(\S+)$/);
        return match ? match[1] : '';
    },

    scheduleLookup() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => this.lookup(), 350);
    },

    /**
     * Check the caret is at the end and fetch suggestions for the last word.
     */
    async lookup() {
        if (this.$refs.input.selectionStart !== this.$refs.input.value.length) {
            this.close();
            return;
        }

        const word = this.currentWord();
        if (word.length < 2) {
            this.close();
            return;
        }

        // Drop any suggestions already present in the query (avoid duplicates).
        const used = this.value.split(/\s+/).map((token) => token.toLowerCase());

        try {
            if (this.controller) {
                this.controller.abort();
            }
            const controller = new AbortController();
            this.controller = controller;

            const response = await fetch(`${endpoint}?q=${encodeURIComponent(word)}`, {
                signal: controller.signal,
                headers: { Accept: 'application/json' },
            });

            if (!response.ok || controller.signal.aborted) {
                return;
            }

            const data = await response.json();
            this.suggestions = (data.tags ?? [])
                .filter((tag) => !used.includes(tag.name.toLowerCase()))
                .slice(0, 10);
            this.highlightIndex = this.suggestions.length > 0 ? 0 : -1;
            this.open = this.suggestions.length > 0;
        } catch (error) {
            // Ignore aborted or network errors; just close.
            this.close();
        }
    },

    /**
     * Replace the trailing word in the query with the chosen tag name.
     */
    select(index) {
        const suggestion = this.suggestions[index];
        if (!suggestion) {
            return;
        }

        const trailing = this.currentWord();
        const withoutTrailing = this.value.slice(0, this.value.length - trailing.length);
        const separator = withoutTrailing.endsWith(' ') ? '' : ' ';
        this.value = `${withoutTrailing}${separator}${suggestion.name} `;
        this.close();

        // Keep focus in the field so the user can keep typing.
        this.$nextTick(() => this.$refs.input.focus());
    },

    moveHighlight(direction) {
        if (!this.open || this.suggestions.length === 0) {
            return;
        }

        const count = this.suggestions.length;
        this.highlightIndex = (this.highlightIndex + direction + count) % count;
    },

    close() {
        this.open = false;
        this.suggestions = [];
        this.highlightIndex = -1;
    },
}));

Alpine.start();
