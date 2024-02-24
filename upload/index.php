<?php

chdir("../");
include("common.php");
Debug();

$user = GetUserData();

if ($user == false) {
    Alert("Session expired.");
}

?>

<html>
    <head>
        <base href="../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .panel {
                padding: 1rem;
            }

            .panel__container {
                display: grid;
                grid-template-columns: max-content 1fr 1fr;
            }

            .panel__tabs {
                display: grid;
                grid-template-rows: max-content max-content max-content 1fr max-content;
                background-color: #111;
                padding-top: 1rem;
            }

            .panel__tabs__text {
                padding: 1rem;
            }

            .panel__tabs__tags {
                padding: 1rem;
            }

            .panel__tabs__description {
                padding: 1rem;
            }

            .panel__tab {
                font-size: 2rem;
                background-color: #111;
                transition-duration: 0.1s;
                user-select: none;
                cursor: pointer;
            }

            .panel__tab:hover {
                background-color: #222;
            }

            .panel__sections {
                padding: 1rem;
                border-radius: 1rem;
                overflow: hidden;
                background-color: #222;
            }

            .panel__section > textarea {
                height: 30rem;
            }

            .panel__image {
                padding: 1rem;
            }

            .panel__image > img {
                width: 100%;
                cursor: pointer;
                border-radius: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="main__upload">
            <?=SetHeader()?>
            <div class="panel">
                <div class="panel__container">
                    <div class="panel__tabs">
                        <div class="panel__tabs__text panel__tab" onclick="btnTab(this)" value="text">
                            Text
                        </div>
                        <div class="panel__tabs__tags panel__tab" onclick="btnTab(this)" value="tags">
                            Tags
                        </div>
                        <div class="panel__tabs__description panel__tab" onclick="btnTab(this)" value="description">
                            Description
                        </div>
                        <div></div>
                        <div class="panel__tabs__upload -center">
                            <button class="-button" onclick="btnSubmit(this)">
                                Upload
                            </button>
                        </div>
                    </div>
                    <div class="panel__sections">
                        <div class="panel__sections__text panel__section">
                            <textarea class="-textarea" placeholder="Enter text that the image may contain..."></textarea>
                        </div>
                        <div class="panel__sections__tags panel__section">
                            <textarea class="-textarea" placeholder="Enter tags that the image may contain..."></textarea>
                        </div>
                        <div class="panel__sections__description panel__section">
                            <textarea class="-textarea" placeholder="Enter additional information that you may know about the image..."></textarea>
                        </div>
                    </div>
                    <div class="panel__image">
                        <img src="assets/image2.png" onclick="btnUpload(this)">
                    </div>
                </div>
            </div>
        </div>
        <form action="server.php" method="post" enctype="multipart/form-data" style="display: none;">
            <textarea name="text"></textarea>
            <textarea name="tags"></textarea>
            <textarea name="description"></textarea>
            <input type="file" accept="image/*" name="image">
            <input name="method" value="upload">
        </form>
    </body>
    <script src="script.js"></script>
    <script>
        document.querySelector(".panel__tabs__text").click();

        function btnTab(element) {
            for (let el of document.querySelectorAll(".panel__section")) {
                el.style.display = "none";
            }

            for (let el of document.querySelectorAll(".panel__tab")) {
                el.style.backgroundColor = "";
            }

            element.style.backgroundColor = "#222";

            switch (element.getAttribute("value")) {
                case "text":
                    document.querySelector(".panel__sections__text").style.display = "";
                    break;
                case "tags":
                    document.querySelector(".panel__sections__tags").style.display = "";
                    break;
                case "description":
                    document.querySelector(".panel__sections__description").style.display = "";
                    break;
            }
        }

        function btnSubmit(element) {
            document.querySelector("body > form > textarea[name='text']").value = document.querySelector(".panel__sections__text > textarea").value;
            document.querySelector("body > form > textarea[name='tags']").value = document.querySelector(".panel__sections__tags > textarea").value;
            document.querySelector("body > form > textarea[name='description']").value = document.querySelector(".panel__sections__description > textarea").value;
            document.querySelector("body > form").submit();
        }

        function btnUpload(element) {
            document.querySelector("body > form > input[name='image']").click();

            document.querySelector("body > form > input[name='image']").onchange = () => {
                document.querySelector(".panel__image > img").src = URL.createObjectURL(event.target.files[0]);

                document.querySelector(".panel__image > img").onload = () => {
                    URL.revokeObjectURL(document.querySelector(".panel__image > img").src);
                }
            }
        }
    </script>
</html>