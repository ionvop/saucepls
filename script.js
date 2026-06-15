const g_inputTags = document.getElementById("g_inputTags");
const g_panelSuggestions = document.getElementById("g_panelSuggestions");
let g_timeout;

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

g_inputTags.oninput = async () => {
    clearTimeout(g_timeout);
    g_timeout = setTimeout(g_loadSuggestions, 1000);
}

async function g_loadSuggestions() {
    const currentTag = g_inputTags.value.split(/\s+/).pop();

    if (!currentTag) {
        g_panelSuggestions.style.display = "none";
        return;
    }

    const response = await fetch("server.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            method: "api",
            action: "suggest_tags",
            tag: currentTag
        })
    });

    const tags = await response.json();
    g_renderSuggestions(tags);
}

function g_renderSuggestions(tags) {
    g_panelSuggestions.innerHTML = "";

    if (!tags.length) {
        g_panelSuggestions.style.display = "none";
        return;
    }

    tags.forEach(tag => {
        const div = document.createElement("div");

        div.innerHTML = /*html*/`
            <div style="
                display: grid;
                grid-template-columns: 1fr max-content;"
                class="-tab"
                data-tag="${tag.name}"
                onclick="g_insertTag(this)">
                <div style="
                    padding: 1rem;">
                    ${tag.name}
                </div>
                <div style="
                    display: flex;
                    align-items: center;
                    padding: 1rem;
                    padding-left: 0rem;
                    font-size: 0.7rem;
                    color: #aaa;">
                    ${tag.count}
                </div>
            </div>
        `;

        g_panelSuggestions.appendChild(div.firstElementChild);
    });

    g_panelSuggestions.style.display = "block";
}

function g_insertTag(element) {
    const tagName = element.dataset.tag;
    const parts = g_inputTags.value.trim().split(/\s+/);

    if (parts.length == 0) {
        g_inputTags.value = tagName + " ";
    } else {
        parts[parts.length - 1] = tagName;
        g_inputTags.value = parts.join(" ") + " ";
    }

    g_panelSuggestions.style.display = "none";
    g_inputTags.focus();
}