<?php

chdir("../");
include("common.php");
Debug();

?>

<html>
    <head>
        <title>
            Upload | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            label {
                font-size: 2rem;
            }

            .-textarea {
                margin-top: 1rem;
            }

            .upload-form {
                margin-top: 3rem;
            }

            .upload-form__container {
                display: grid;
                grid-template-columns: 40% 1fr;
                padding: 3rem;
                background-color: #111;
                border-radius: 3rem;
            }

            .upload-form__info {
                padding: 3rem;
                height: 30rem;
                overflow-y: auto;
            }

            .upload-form__info__text {
                margin-top: 3rem;
            }

            .upload-form__info__tags {
                margin-top: 3rem;
            }

            .upload-form__image {
                padding: 3rem;
            }

            .upload-form__image img {
                max-width: 100%;
                max-height: 30rem;
                border-radius: 1rem;
            }

            .upload-form__details {
                padding: 5rem;
            }
        </style>
    </head>
    <body>
        <div class="main__upload">
            <?=SetHeader()?>
            <div class="-content">
                <div class="-content__page">
                    <div class="-title -center">
                        Upload
                    </div>
                    <div class="upload-form">
                        <form action="server.php" method="post" enctype="multipart/form-data">
                            <div class="upload-form__container">
                                <div class="upload-form__info">
                                    <div class="upload-form__info__text">
                                        <label for="text">
                                            Text:
                                        </label>
                                        <textarea name="text" class="-textarea -script__textarea--dynamic" placeholder="what women think we want what we really want"></textarea>
                                    </div>
                                    <div class="upload-form__info__tags">
                                        <label for="tags">
                                            Tags:
                                        </label>
                                        <textarea name="tags" class="-textarea -script__textarea--dynamic" placeholder="meme 1girl black lace trimmed bra panties"></textarea>
                                    </div>
                                </div>
                                <div class="upload-form__image -center">
                                    <input type="file" name="image" accept=".jpg, .png" onchange="UpdatePreview()" style="display: none;" class="-input">
                                    <div class="image__preview">
                                        <img src="assets/image.png" onclick="btnUpload()">
                                    </div>
                                </div>
                            </div>
                            <div class="upload-form__details">
                                <label for="details">
                                    Details:
                                </label>
                                <textarea name="details" class="-textarea -script__textarea--dynamic"></textarea>
                            </div>
                            <div class="upload-form__submit -center">
                                <button name="method" value="upload" class="-button">
                                    Upload
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>
        function btnUpload() {
            q("input[name=\"image\"]").click();
        }

        function UpdatePreview() {
            let output = q(".image__preview img");
            output.src = URL.createObjectURL(event.target.files[0]);

            output.onload = () => {
                URL.revokeObjectURL(output.src) // free memory
            }
        }
    </script>
</html>