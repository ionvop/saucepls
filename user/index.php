<?php

chdir("../");
include("common.php");
Debug();
$data = GetSiteData();
$userIndex = FindIndexByKeyValue($data->users, "id", AuthenticateUser());

if ($userIndex == -1) {
    $user = new stdClass();
        $user->id = "";
} else {
    $user = $data->users[$userIndex];
}

$displayIndex = FindIndexByKeyValue($data->users, "username", $_GET["id"]);

if ($displayIndex == -1) {
    Alert("User not found");
}

$display = $data->users[$displayIndex];

?>

<html>
    <head>
        <title>
            <?=htmlentities($display->username)?> | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .info {
                display: grid;
                grid-template-columns: max-content 1fr max-content;
            }

            .info__avatar {
                padding: 1rem;
            }

            .info__avatar > img {
                width: 15rem;
                height: 15rem;
                border-radius: 1rem;
            }

            .info__details {
                padding: 1rem;
            }

            .info__details__type {
                margin-top: 1rem;
            }

            .info__details__description {
                margin-top: 1rem;
                background-color: #111;
                border-radius: 1rem;
                padding: 1rem;
                min-height: 8rem;
            }

            .info__actions {
                padding: 1rem;
                text-align: center;
            }

            .info__actions__following-count {
                margin-top: 2rem;
            }

            .info__actions__follower-count {
                margin-top: 1rem;
            }

            .info__actions__settings {
                margin-top: 3rem;
            }
        </style>
    </head>
    <body>
        <div class="main__user">
            <?=SetHeader()?>
            <div class="-content">
                <div class="-content__page">
                    <div class="info">
                        <div class="info__avatar">
                            <img src="uploads/avatar/<?=$display->avatar?>">
                        </div>
                        <div class="info__details">
                            <div class="info__details__name -title">
                                <?=htmlentities($display->username)?>
                            </div>
                            <div class="info__details__type -subtitle">
                                <?=ucfirst($display->type)?>
                            </div>
                            <div class="info__details__description">
                                <?=str_replace("\n", "<br>", htmlentities($display->description))?>
                            </div>
                        </div>
                        <div class="info__actions">
                            <?php
                                if ($display->id != $user->id && $userIndex != -1) {
                                    echo <<<HTML
                                        <div class="info__actions__follow">
                                            <button class="-button">
                                                Follow
                                            </button>
                                        </div>
                                    HTML;
                                }
                            ?>
                            <div class="info__actions__following-count -subtitle">
                                Following: 0
                            </div>
                            <div class="info__actions__follower-count -subtitle">
                                Followers: 0
                            </div>
                            <?php
                                if ($display->id == $user->id) {
                                    echo <<<HTML
                                        <div class="info__actions__settings -button--icon">
                                            <a href="user/settings/">
                                                <span class="material-symbols-rounded">
                                                    settings
                                                </span>
                                            </a>
                                        </div>
                                    HTML;
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
</html>