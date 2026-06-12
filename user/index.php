<?php

chdir("../");
require_once "common.php";

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
            box-sizing: border-box;">
            <?= renderNavigation() ?>
            <div style="
                display: grid;
                grid-template-rows: max-content 1fr;">
                <?= renderHeader() ?>
                <div style="
                    display: grid;
                    grid-template-columns: 1fr max-content;">
                    <div>

                    </div>
                    <div style="
                        background-color: #222;
                        border-left: 1px solid #555;
                        width: 15rem;">

                    </div>
                </div>
            </div>
        </div>
    </body>
</html>