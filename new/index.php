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
                height: 40rem;
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
                height: 100%;
            }

            .new__panels__upload__box > button {
                display: grid;
                grid-template-columns: repeat(2, max-content);
            }

            .new__panels__upload__box__button__icon > svg {
                width: 1.5rem;
                height: 1.5rem;
            }

            .new__panels__upload__box__button__text {
                padding-left: 1rem;
            }

            .new__panels__text__box {
                display: grid;
                grid-template-rows: max-content 1fr;
            }

            .new__panels__tags__box {
                display: grid;
                grid-template-rows: repeat(2, max-content) 1fr;
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

            .new__panels__tags__box__list__box {
                padding: 1rem;
                height: 90%;
                border-radius: 1rem;
                background-color: #111;
            }

            .new__panels__description__box {
                display: grid;
                grid-template-rows: max-content 1fr;
            }

            .new__preview {
                padding: 1rem;
            }

            .new__preview__box {
                display: grid;
                grid-template-rows: max-content 1fr max-content;
                padding: 1rem;
                height: 40rem;
                border-radius: 1rem;
                background-color: #222;
            }

            .new__preview__box__label {
                padding: 1rem;
            }

            .new__preview__box__image {
                padding: 1rem;
                overflow: hidden;
            }

            .new__preview__box__image > img {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }

            .new__preview__box__submit {
                padding: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="main__new">
            <?=SetHeader()?>
            <div class="content">
                <div class="new">
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
                                <button class="-button">
                                    <div class="new__panels__upload__box__button__icon">
                                        <?=Icon("upload")?>
                                    </div>
                                    <div class="new__panels__upload__box__button__text">
                                        Upload Image
                                    </div>
                                </button>
                                <input type="file" name="image" accept="image/*" style="display: none;">
                            </div>
                        </div>
                        <div class="new__panels__text new__panel">
                            <div class="new__panels__text__box new__panel__box">
                                <div class="new__panels__text__box__label new__panel__box__label">
                                    Text:
                                </div>
                                <div class="new__panels__text__box__input new__panel__box__input">
                                    <textarea name="text" class="-textarea" placeholder="What text does the image contain?"></textarea>
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
                                        <input class="-input" placeholder="Add a tag...">
                                    </div>
                                    <div class="new__panels__tags__box__field__button">
                                        <button class="-button">
                                            Add
                                        </button>
                                    </div>
                                </div>
                                <div class="new__panels__tags__box__list">
                                    <div class="new__panels__tags__box__list__box"></div>
                                </div>
                            </div>
                        </div>
                        <div class="new__panels__description new__panel">
                            <div class="new__panels__description__box new__panel__box">
                                <div class="new__panels__description__box__label new__panel__box__label">
                                    Description:
                                </div>
                                <div class="new__panels__description__box__input new__panel__box__input">
                                    <textarea name="description" class="-textarea" placeholder="What can you say about this image?"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="new__preview">
                        <div class="new__preview__box">
                            <div class="new__preview__box__label">
                                Preview:
                            </div>
                            <div class="new__preview__box__image">
                                <img src="assets/image.png">
                            </div>
                            <div class="new__preview__box__submit -center">
                                <button class="-button">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
    </script>
</html>