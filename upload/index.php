<?php

chdir("../");
require_once "common.php";
$user = getUser();

if ($user == false) {
    header("Location: login/");
}

?>

<html>
    <head>
        <title>
            User | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>

        </style>
    </head>
    <body>
        <div style="
            display: grid;
            grid-template-columns: max-content 1fr;
            height: 100%;
            box-sizing: border-box;
            overflow: hidden;">
            <?= renderNavigation() ?>
            <div style="
                display: grid;
                grid-template-rows: max-content 1fr;
                overflow: hidden;">
                <?= renderHeader() ?>
                <form style="
                    display: grid;
                    grid-template-columns: repeat(2, 1fr) max-content;
                    overflow: hidden;"
                    action="server.php"
                    method="post"
                    enctype="multipart/form-data">
                    <div style="
                        display: grid;
                        grid-template-rows: max-content 1fr max-content;
                        border-right: 1px solid #555;
                        overflow: hidden;">
                        <div style="
                            padding: 1rem;
                            font-size: 1.5rem;">
                            Upload image
                        </div>
                        <div style="
                            padding: 1rem;
                            overflow: hidden;">
                            <img style="
                                width: 100%;
                                height: 100%;
                                object-fit: contain;
                                cursor: pointer;"
                                src="assets/image.png"
                                id="imgPreview">
                            <input style="
                                display: none;"
                                type="file"
                                accept="image/*"
                                name="image"
                                id="inputImage"
                                required>
                        </div>
                        <div style="
                            padding: 1rem;
                            font-size: 0.7rem;
                            color: #aaa;
                            text-align: center;">
                            Click or drag an image over to upload
                        </div>
                    </div>
                    <div style="
                        overflow: auto;">
                        <div style="
                            padding: 1rem;">
                            Title
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;">
                            <input name="title"
                                placeholder="Sauce pls..."
                                required>
                        </div>
                        <div style="
                            padding: 1rem;">
                            Description
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;">
                            <textarea name="description"
                                placeholder="Provide more context about how and where you found this image..."></textarea>
                        </div>
                        <div style="
                            padding: 1rem;">
                            Tags
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;">
                            <textarea name="tags"
                                placeholder="1girl black_hair red_eyes..."
                                id="g_inputTags"></textarea>
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;">
                            <div style="
                                display: none;
                                background-color: #111;
                                border-radius: 1rem;
                                overflow: hidden;"
                                id="g_panelSuggestions">

                            </div>
                        </div>
                        <div style="
                            padding: 1rem;">
                            Text found in image
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;">
                            <textarea name="text"
                                placeholder="Leave blank if image doesn't contain text..."></textarea>
                        </div>
                    </div>
                    <div style="
                        background-color: #222;
                        border-left: 1px solid #555;
                        width: 15rem;">
                        <div style="
                            padding: 1rem;
                            text-align: center;">
                            <button name="method"
                                value="upload">
                                <div style="
                                    display: grid;
                                    grid-template-columns: max-content 1fr;">
                                    <div style="
                                        display: flex;
                                        align-items: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z"/></svg>
                                    </div>
                                    <div style="
                                        display: flex;
                                        align-items: center;
                                        padding-left: 0.5rem;">
                                        Submit
                                    </div>
                                </div>
                            </button>
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;
                            text-align: center;">
                            <button style="
                                background-color: #555;"
                                type="button"
                                id="btnCancel">
                                <div style="
                                    display: grid;
                                    grid-template-columns: max-content 1fr;">
                                    <div style="
                                        display: flex;
                                        align-items: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z"/></svg>
                                    </div>
                                    <div style="
                                        display: flex;
                                        align-items: center;
                                        padding-left: 0.5rem;">
                                        Cancel
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script src="script.js"></script>
        <script>
            const imgPreview = document.getElementById("imgPreview");
            const inputImage = document.getElementById("inputImage");
            const btnCancel = document.getElementById("btnCancel");

            imgPreview.onclick = () => {
                inputImage.click();
            }

            imgPreview.ondragover = (e) => {
                e.preventDefault();
                imgPreview.style.opacity = "0.6";
            };

            imgPreview.ondragleave = () => {
                imgPreview.style.opacity = "1";
            };

            imgPreview.ondrop = (e) => {
                e.preventDefault();
                imgPreview.style.opacity = "1";

                const file = e.dataTransfer.files[0];
                if (!file || !file.type.startsWith("image/")) return;

                // Sync with the file input so the form still submits correctly
                const dt = new DataTransfer();
                dt.items.add(file);
                inputImage.files = dt.files;

                imgPreview.src = URL.createObjectURL(file);
            };

            inputImage.onchange = () => {
                if (inputImage.files.length == 0) {
                    imgPreview.src = "assets/image.png";
                    return;
                }

                imgPreview.src = URL.createObjectURL(inputImage.files[0]);
            }

            btnCancel.onclick = () => {
                if (confirm("Are you sure you want to cancel?")) {
                    location.href = "./";
                }
            }
        </script>
    </body>
</html>