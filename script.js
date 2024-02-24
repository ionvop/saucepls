function btnHeaderTitle(element) {
    location.href = "./";
}

function btnHeaderLogin(element) {
    location.href = "login/";
}

function btnHeaderRegister(element) {
    location.href = "register/";
}

function btnHeaderSearch(element) {
    element.parentElement.querySelector(".-header__search__button > form > input[name='q']").value = element.parentElement.parentElement.querySelector(".-header__search__input > input").value;
    element.parentElement.querySelector(".-header__search__button > form").submit();
}

function btnHeaderUpload(element) {
    location.href = "upload/";
}

function btnHeaderProfile(element) {
    formSubmit("user/", "get", {
        id: element.getAttribute("value")
    });
}

function btnHeaderLogout(element) {
    formSubmit("server.php", "post", {
        method: "logout"
    })
}

function formSubmit(url, method, data) {
    let form = document.createElement('form');
    form.style.display = "none";
    form.action = url;
    form.method = method;

    if (method == "post") {
        form.enctype = 'multipart/form-data';
    }

    for (let key in data) {
        let input = document.createElement("input");
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}