function q(query) {
    return document.querySelector(query);
}

function btnLogout() {
    if (confirm("Are you sure you want to logout?")) {
        EraseCookie("sessionid");
        location.reload();
    }
}

function EraseCookie(name) {   
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

for (let element of document.querySelectorAll(".-script__textarea--dynamic")) {
    element.oninput = () => {
        element.style.resize = "none";
        element.style.height = "";
        element.style.height = element.scrollHeight + "px"
    }
}