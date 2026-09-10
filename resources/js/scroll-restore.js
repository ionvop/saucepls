/**
 * Global, path-aware scroll restoration.
 *
 * Full-page form submissions (bookmarking, liking, etc.) that redirect back to
 * the same page are treated by the browser as a new navigation, so the native
 * scroll restoration does not kick in and the page jumps to the top.
 *
 * This module records the user's scroll position before leaving the page and,
 * on the next load, restores it only when the previous page was the same path
 * (i.e. a same-page reload). Navigating to a different page never restores.
 *
 * Positions are kept in sessionStorage, so they are scoped to the current tab
 * and cleared when the tab closes.
 */

const PREV_PATH_KEY = 'prevPath';
const SCROLL_KEY = 'scrollPositions';

const currentPath = window.location.pathname;

// --- Restore on load, only if the previous page was the same path ---
const prevPath = sessionStorage.getItem(PREV_PATH_KEY);
const positions = JSON.parse(sessionStorage.getItem(SCROLL_KEY) || '{}');

if (prevPath === currentPath && typeof positions[currentPath] === 'number') {
    // Defer so the DOM/layout is ready before scrolling.
    requestAnimationFrame(() => window.scrollTo(0, positions[currentPath]));
}

// --- Record scroll position (throttled, as a backup) ---
let scrollTimer;
window.addEventListener(
    'scroll',
    () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(() => {
            positions[currentPath] = window.scrollY;
            sessionStorage.setItem(SCROLL_KEY, JSON.stringify(positions));
        }, 200);
    },
    { passive: true }
);

// --- On leaving the page, save position + remember this path as "previous" ---
window.addEventListener('pagehide', () => {
    positions[currentPath] = window.scrollY;
    sessionStorage.setItem(SCROLL_KEY, JSON.stringify(positions));
    sessionStorage.setItem(PREV_PATH_KEY, currentPath);
});
