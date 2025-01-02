<?php

chdir("../");
include("common.php");
Debug();

?>

<html>
    <head>
        <title>
            New Request | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .content {
                padding: 1rem;
            }

            .new {
                display: grid;
                grid-template-columns: max-content repeat(2, 1fr);
            }

            .new__tab {
                padding: 1rem;
            }

            .new__tab__box {
                padding: 1rem;
                border-radius: 1rem;
                cursor: pointer;
            }

            .new__tab__box--active {
                background-color: #222;
            }

            .new__panel {
                display: none;
                padding: 1rem;
            }

            .new__panel--active {
                display: block;
            }

            .new__panel__box {
                padding: 1rem;
                border-radius: 1rem;
                background-color: #222;
            }

            .new__panel__box__label {
                padding: 1rem;
            }

            .new__panel__box__input {
                padding: 1rem;
            }

            .new__panel__box__input > textarea {
                height: 20rem;
            }

            .new__panels__upload__box {
                height: 20rem;
            }

            .new__panels__tags__box__field {
                display: grid;
                grid-template-columns: 1fr max-content;
            }

            .new__panels__tags__box__field__input {
                padding: 1rem;
            }

            .new__panels__tags__box__field__button {
                padding: 1rem;
            }

            .new__panels__tags__box__list {
                padding: 1rem;
                overflow: hidden;
            }

            .new__panels__tags__box__list__render {
                padding: 1rem;
                height: 20rem;
                border-radius: 1rem;
                background-color: #111;
            }

            .new__panels__tags__box__list__render .item {
                display: grid;
                grid-template-columns: 1fr max-content;
            }

            .new__panels__tags__box__list__render .item__tag {
                display: flex;
                align-items: center;
                padding: 1rem;
            }

            .new__panels__tags__box__list__render .item__remove {
                padding: 1rem;
            }

            .new__panels__tags__box__list__render .item__remove > svg {
                width: 2rem;
                height: 2rem;
                cursor: pointer;
            }

            .new__panels__description__box {
                display: grid;
                grid-template-rows: max-content 1fr;
            }

            .new__preview {
                padding: 1rem;
            }

            .new__preview__label {
                padding: 1rem;
            }

            .new__preview__image {
                padding: 1rem;
                overflow: hidden;
            }

            .new__preview__image > img {
                width: 100%;
                height: 30rem;
                object-fit: contain;
            }

            .new__preview__submit {
                visibility: hidden;
                padding: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="main__new">
            <?=SetHeader()?>
            <div class="content">
                <form class="-form new" action="server.php" method="post" enctype="multipart/form-data">
                    <div class="new__tabs">
                        <div class="new__tabs__upload new__tab">
                            <div class="new__tabs__upload__box new__tab__box new__tab__box--active" onclick="btnTab(this)" data-tab="upload">
                                Upload
                            </div>
                        </div>
                        <div class="new__tabs__text new__tab">
                            <div class="new__tabs__text__box new__tab__box" onclick="btnTab(this)" data-tab="text">
                                Text
                            </div>
                        </div>
                        <div class="new__tabs__tags new__tab">
                            <div class="new__tabs__tags__box new__tab__box" onclick="btnTab(this)" data-tab="tags">
                                Tags
                            </div>
                        </div>
                        <div class="new__tabs__description new__tab">
                            <div class="new__tabs__description__box new__tab__box" onclick="btnTab(this)" data-tab="description">
                                Description
                            </div>
                        </div>
                    </div>
                    <div class="new__panels">
                        <div class="new__panels__upload new__panel new__panel--active">
                            <div class="new__panels__upload__box new__panel__box -center__flex">
                                <button class="-button" type="button" onclick="btnUpload(this)">
                                    <div class="-iconlabel">
                                        <div class="-iconlabel__icon">
                                            <?=Icon("upload")?>
                                        </div>
                                        <div class="-iconlabel__text">
                                            Upload Image
                                        </div>
                                    </div>
                                </button>
                                <input type="file" name="image" accept="image/*" style="display: none;" required>
                            </div>
                        </div>
                        <div class="new__panels__text new__panel">
                            <div class="new__panels__text__box new__panel__box">
                                <div class="new__panels__text__box__label new__panel__box__label">
                                    Text:
                                </div>
                                <div class="new__panels__text__box__input new__panel__box__input">
                                    <textarea name="text" class="-textarea" placeholder="What text does the image contain?" maxlength="1000"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="new__panels__tags new__panel">
                            <div class="new__panels__tags__box new__panel__box">
                                <div class="new__panels__tags__box__label new__panel__box__label">
                                    Tags:
                                </div>
                                <div class="new__panels__tags__box__field">
                                    <div class="new__panels__tags__box__field__input">
                                        <input class="-input" placeholder="Add a tag..." oninput="inputTagChange(this)" onkeydown="inputTagEnter(this, event)" maxlength="20">
                                    </div>
                                    <div class="new__panels__tags__box__field__button">
                                        <button class="-button" type="button" onclick="btnAddTag(this)">
                                            Add
                                        </button>
                                    </div>
                                </div>
                                <div class="new__panels__tags__box__list">
                                    <div class="new__panels__tags__box__list__render"></div>
                                </div>
                            </div>
                        </div>
                        <div class="new__panels__description new__panel">
                            <div class="new__panels__description__box new__panel__box">
                                <div class="new__panels__description__box__label new__panel__box__label">
                                    Description:
                                </div>
                                <div class="new__panels__description__box__input new__panel__box__input">
                                    <textarea name="description" class="-textarea" placeholder="What can you say about this image?" maxlength="1000"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="new__preview">
                        <div class="new__preview__label">
                            Preview:
                        </div>
                        <div class="new__preview__image">
                            <img src="assets/image.png">
                        </div>
                        <div class="new__preview__submit -center">
                            <button class="-button" name="method" value="newRequest">
                                Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>
        function btnTab(element) {
            let tab = element.dataset.tab;
            let tabs = document.querySelectorAll(".new__tab__box");
            let panels = document.querySelectorAll(".new__panel");

            tabs.forEach((element) => {
                element.classList.remove("new__tab__box--active");
            });

            panels.forEach((element) => {
                element.classList.remove("new__panel--active");
            });

            element.classList.add("new__tab__box--active");
            document.querySelector(`.new__panels__${tab}`).classList.add("new__panel--active");
        }

        function btnUpload(element) {
            let input = document.querySelector(".new__panels__upload__box > input");
            input.click();

            input.addEventListener("change", () => {
                let preview = document.querySelector(".new__preview__image > img");
                let submit = document.querySelector(".new__preview__submit");
                preview.src = URL.createObjectURL(input.files[0]);
                submit.style.visibility = "visible";
            });
        }

        function btnAddTag(element) {
            let input = document.querySelector(".new__panels__tags__box__field__input > input");
            let list = document.querySelector(".new__panels__tags__box__list__render");

            if (input.value == "") {
                return;
            }

            let item = /* html */`
                <div class="item">
                    <div class="item__tag">
                        ${input.value}
                    </div>
                    <div class="item__remove">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor" onclick="btnRemoveTag(this)">
                            <path d="M240-440q-17 0-28.5-11.5T200-480q0-17 11.5-28.5T240-520h480q17 0 28.5 11.5T760-480q0 17-11.5 28.5T720-440H240Z"/>
                        </svg>
                    </div>
                    <input type="hidden" name="tags[]" value="${input.value}">
                </div>
            `;

            list.insertAdjacentHTML("beforeend", item);
            let items = Array.from(list.querySelectorAll(".item"));

            items.sort((a, b) => {
                let tagA = a.querySelector(".item__tag").textContent.trim().toLowerCase();
                let tagB = b.querySelector(".item__tag").textContent.trim().toLowerCase();
                return tagA.localeCompare(tagB);
            });

            list.innerHTML = "";
            items.forEach(item => list.appendChild(item));
            input.value = "";
        }

        function btnRemoveTag(element) {
            let item = element.parentElement.parentElement;
            item.remove();
        }

        function inputTagChange(element) {
            if (element.value.substring(0, 1) == "-") {
                element.value = element.value.substring(1);
            }

            element.value = element.value.replace(/[^a-zA-Z0-9_-]/g, "");
        }

        function inputTagEnter(element, event) {
            if (event.key == "Enter") {
                event.preventDefault();
                btnAddTag();
            }
        }
    </script>
</html>