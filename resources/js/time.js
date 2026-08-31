/**
 * Client-side timezone rendering for datetimes.
 *
 * The server renders every timestamp as an ISO-8601 UTC string inside a
 * `data-time` attribute, keeping the original UTC text as a no-JavaScript
 * fallback. On load this module replaces that text with a version formatted
 * in the visitor's local timezone using `Intl.DateTimeFormat`.
 *
 * Supported `data-format` values:
 *   - `date`       -> "M j, Y"            (e.g. "Aug 31, 2026")
 *   - `datetime`   -> "M j, Y g:i A"      (e.g. "Aug 31, 2026 3:45 PM")
 *   - `month-year` -> "F Y"               (e.g. "August 2026")
 *   - `relative`   -> "3 hours ago"       (replaces diffForHumans())
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
 * Human-friendly relative time, e.g. "just now", "5 minutes ago", "2 days ago".
 *
 * @param {Date} date
 * @returns {string}
 */
function relativeTime(date) {
    const seconds = Math.round((date.getTime() - Date.now()) / 1000);
    const abs = Math.abs(seconds);

    const units = [
        ['year', 31536000],
        ['month', 2592000],
        ['week', 604800],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
    ];

    for (const [unit, secondsInUnit] of units) {
        if (abs >= secondsInUnit) {
            const value = Math.round(seconds / secondsInUnit);
            return `${value} ${unit}${Math.abs(value) === 1 ? '' : 's'} ago`;
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

    // Keep the original UTC value as a tooltip for absolute formats.
    if (format !== 'relative') {
        el.title = formatDate(date, 'datetime');
    }

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