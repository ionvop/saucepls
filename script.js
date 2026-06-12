(() => {
    for (let element of document.querySelectorAll("*")) {
        element.style.setProperty("--test", "test");
        element.style.removeProperty("--test");

        // check if element has data-timestamp attribute
        if (element.hasAttribute("data-timestamp")) {
            element.title = new Date(element.getAttribute("data-timestamp") * 1000).toLocaleString();
        }
    }
})();