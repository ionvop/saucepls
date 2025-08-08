<?php

include "common.php";

?>

<html>
    <head>
        <title>
            SaucePls
        </title>
        <base href="./">
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>

        </style>
    </head>
    <body>
        <div class="main -main">
            <?=renderNavigation()?>
            <div class="page">
                <?=renderHeader()?>
                <div class="content -pad">
                    <div class="welcome -pad -title -center">
                        Welcome to SaucePls
                    </div>
                    <div class="subtitle -pad -center">
                        Upload images, anime screenshots, or manga panels and let the community help you find the source or artist!
                    </div>
                </div>
            </div>
        </div>
        <script src="script.js"></script>
        <script>

        </script>
    </body>
</html>