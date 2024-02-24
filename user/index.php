<?php

chdir("../");
include("common.php");
Debug();

$user = GetUserData();

if (isset($_GET["id"]) == false) {
    $_GET["id"] = $user["username"];
}

$displayUser = GetOtherUserData($_GET["id"]);

?>

<html>
    <head>
        <base href="../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .userpanel {
                display: grid;
                grid-template-columns: max-content 1fr;
            }

            .userpanel__info__avatar {
                padding: 1rem;
            }

            .userpanel__info__avatar > img {
                width: 10rem;
                height: 10rem;
                border-radius: 1rem;
            }

            .userpanel__info__username {
                padding: 1rem;
                font-size: 2rem;
            }

            .userpanel__info__type {
                padding: 1rem;
                font-size: 1.5rem;
                color: #aaa;
            }

            .userpanel__info__details {
                padding: 1rem;
                font-size: 1rem;
                color: #555;
            }

            .userpanel__info__follow {
                padding: 1rem;
            }
            
            .userpanel__info__settings {
                padding: 1rem;
            }

            .userpanel__details {
                padding: 1rem;
            }

            .userpanel__details__description {
                padding: 1rem;
                min-height: 100%;
                border-radius: 1rem;
                background-color: #222;
                white-space: pre;
            }
        </style>
    </head>
    <body>
        <div class="main__user">
            <?=SetHeader()?>
            <div class="userpanel">
                <div class="userpanel__info">
                    <div class="userpanel__info__avatar -center">
                        <img src="uploads/avatar/<?=htmlentities($displayUser["avatar"])?>">
                    </div>
                    <div class="userpanel__info__username -center">
                        <?=htmlentities($displayUser["username"])?>
                    </div>
                    <div class="userpanel__info__type -center">
                        <?=htmlentities(ucfirst($displayUser["type"]))?>
                    </div>
                    <div class="userpanel__info__details -center">
                        Following: 0<br>
                        Followers: 0<br>
                        Joined on <?=htmlentities(date("Y-m-d", $displayUser["time"]))?>
                    </div>
                    <?php
                        if ($user != false) {
                            if ($displayUser["id"] != $user["id"]) {
                                echo <<<HTML
                                    <div class="userpanel__info__follow -center">
                                        <button class="-button">
                                            Follow
                                        </button>
                                    </div>
                                HTML;
                            } else {
                                echo <<<HTML
                                    <div class="userpanel__info__settings -center">
                                        <button class="-button" onclick="btnSettings(this)">
                                            Settings
                                        </button>
                                    </div>
                                HTML;
                            }
                        }
                    ?>
                </div>
                <div class="userpanel__details">
                    <div class="userpanel__details__description"><?=htmlentities($displayUser["description"])?></div>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>
        function btnSettings(element) {
            location.href = "user/edit/";
        }
    </script>
</html>