(() => {
    for (let element of document.querySelectorAll("*")) {
        element.style.setProperty("--test", "test");
        element.style.removeProperty("--test");
    }
})();