<?php

chdir("../");
include "common.php";

?>

<html>
    <head>
        <title>
            Upload | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body > .main {
                & > .page {
                    & > .content {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        overflow: hidden;

                        & > .upload {
                            overflow: auto;
                        }

                        & > .preview {
                            & > img {
                                width: 100%;
                                height: 100%;
                                object-fit: contain;
                            }
                        }
                    }
                }
            }
        </style>
    </head>
    <body>
        <div class="main -main">
            <?=renderNavigation()?>
            <div class="page">
                <?=renderHeader()?>
                <div class="content">
                    <form action="server.php" class="-form upload -pad" method="post" enctype="multipart/form-data">
                        <div class="title -pad -title -center">
                            Upload
                        </div>
                        <div class="image field -pad">
                            <div class="label -pad">
                                Image
                            </div>
                            <div class="input -pad">
                                <input type="file" accept="image/*" name="image" onchange="previewImage(event)" required>
                            </div>
                        </div>
                        <div class="title field -pad">
                            <div class="label -pad">
                                Title
                            </div>
                            <div class="input -pad">
                                <input class="-input" name="title" placeholder="e.g. Who is this character?" required>
                            </div>
                        </div>
                        <div class="description field -pad">
                            <div class="label -pad">
                                Description / Additional Info
                            </div>
                            <div class="input -pad">
                                <textarea name="description" class="-textarea" placeholder="Any context, where you found the image, etc."></textarea>
                            </div>
                        </div>
                        <div class="tags field -pad">
                            <div class="label -pad">
                                Tags
                            </div>
                            <div class="input -pad">
                                <textarea class="-textarea" name="tags" placeholder="e.g. 1girl, blue_eyes, blonde_hair" required></textarea>
                            </div>
                            <div class="subtitle -subtitle -center">
                                Separate tags with commas.
                            </div>
                        </div>
                        <div class="text field -pad">
                            <div class="label -pad">
                                Text on Image (if any)
                            </div>
                            <div class="input -pad">
                                <textarea class="-textarea" name="text" placeholder="Meme text, caption, speech bubble, etc."></textarea>
                            </div>
                        </div>
                        <div class="send -pad -center">
                            <button class="-button" name="method" value="upload">
                                Upload
                            </button>
                        </div>
                    </form>
                    <div class="preview -pad">
                        <img src="assets/image.png" id="preview">
                    </div>
                </div>
            </div>
        </div>
        <script src="script.js"></script>
        <script>
            function previewImage(event) {
                let reader = new FileReader();
                
                reader.onload = function() {
                    let output = document.getElementById('preview');
                    output.src = reader.result;
                
                    output.onload = function() {
                        URL.revokeObjectURL(output.src)
                    }
                }

                reader.readAsDataURL(event.target.files[0]);
            }
        </script>
    </body>
</html>