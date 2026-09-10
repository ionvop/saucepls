/**
 * Client-side timezone rendering for datetimes.
 *
 * The server renders every timestamp as an ISO-8601 UTC string inside a
 * `data-time` attribute, keeping the original UTC text as a no-JavaScript
 * fallback. On load this module replaces that text with a version formatted
 * in the visitor's local timezone using `Intl.DateTimeFormat`, and sets the
 * absolute datetime as a tooltip (`title`) on the element.
 *
 * Supported `data-format` values:
 *   - `date`       -> "M j, Y"            (e.g. "Aug 31, 2026")
 *   - `datetime`   -> "M j, Y g:i A"      (e.g. "Aug 31, 2026 3:45 PM")
 *   - `month-year` -> "F Y"               (e.g. "August 2026")
 *   - `relative`   -> "4d"                (compact, replaces diffForHumans())
 */

const FORMATS = {
    date: { month: 'short', day: 'numeric', year: 'numeric' },
    datetime: { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' },
    'month-year': { month: 'long', year: 'numeric' },
};

/**
 * Format a Date in the visitor's local timezone.
 *
 * @param {Date} date
 * @param {string} format
 * @returns {string}
 */
function formatDate(date, format) {
    const options = FORMATS[format];

    if (!options) {
        return date.toLocaleString();
    }

    return new Intl.DateTimeFormat(undefined, options).format(date);
}

/**
 * Compact human-friendly relative time, e.g. "just now", "5m", "4h", "4d",
 * "2w", "3mo", "2y". The absolute datetime is exposed as a tooltip via the
 * `title` attribute set in `render()`.
 *
 * @param {Date} date
 * @returns {string}
 */
function relativeTime(date) {
    const seconds = Math.round((date.getTime() - Date.now()) / 1000);
    const abs = Math.abs(seconds);

    const units = [
        ['y', 31536000],
        ['mo', 2592000],
        ['w', 604800],
        ['d', 86400],
        ['h', 3600],
        ['m', 60],
    ];

    for (const [unit, secondsInUnit] of units) {
        if (abs >= secondsInUnit) {
            const value = Math.round(abs / secondsInUnit);
            return `${value}${unit}`;
        }
    }

    return 'just now';
}

/**
 * Render a single `[data-time]` element in the visitor's timezone.
 *
 * @param {HTMLElement} el
 */
function render(el) {
    const raw = el.dataset.time;

    if (!raw) {
        return;
    }

    const date = new Date(raw);

    if (Number.isNaN(date.getTime())) {
        return;
    }

    const format = el.dataset.format || 'date';
    const text = format === 'relative' ? relativeTime(date) : formatDate(date, format);

    // Keep the absolute datetime as a tooltip for every format.
    el.title = formatDate(date, 'datetime');

    el.textContent = text;
}

/**
 * Initialize all `[data-time]` elements on the page.
 */
function init() {
    document.querySelectorAll('[data-time]').forEach(render);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}