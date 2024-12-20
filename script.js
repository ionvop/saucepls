GlobalEventListener("click", ".-script__link", (element, event) => {
    if (event.shiftKey) {
        window.open(element.dataset.href);
        return;
    }

    location.href = element.dataset.href;
});

GlobalEventListener("input", ".-script__alphanum", element => {
    element.value = element.value.replace(/[^a-zA-Z0-9_-]/g, "");
});

function GlobalEventListener(type, selector, callback) {
    document.addEventListener(type, (event) => {
        if (event.target.closest(selector)) {
            callback(event.target.closest(selector), event);
        }
    });
}