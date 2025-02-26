<?php

chdir("../");
include("common.php");
include("lib/Parsedown.php");
Debug();
$user = GetUser();
$target = GetTarget($_GET["id"]);

if ($target == false) {
    Alert("That user does not exist.");
}

$Parsedown = new Parsedown();

switch ($target["type"]) {
    case "admin":
        $memberType = "Administrator";
        break;
    case "moderator":
        $memberType = "Moderator";
        break;
    case "member":
        $memberType = "Member";
        break;
}

?>

<html>
    <head>
        <title>
            <?=$target["username"]?> | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .user {
                display: grid;
                grid-template-columns: 20rem 1fr;
            }

            .user__panel {
                background-color: #222;
            }

            .user__panel__avatar {
                padding: 1rem;
            }

            .user__panel__avatar > img {
                width: 10rem;
                height: 10rem;
                object-fit: cover;
                border-radius: 1rem;
            }

            .user__panel__username {
                padding: 1rem;
                overflow: hidden;
            }

            .user__panel__details {
                padding: 1rem;
                line-height: 2rem;
            }

            .user__panel__actions {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
            }

            .user__panel__action {
                padding: 1rem;
            }

            .user__panel__settings {
                padding: 1rem;
            }

            .user__content {
                overflow: hidden;
            }

            .user__content__description {
                padding: 1rem;
                overflow: hidden;
            }

            .user__content__description a {
                color: var(--theme__light);
            }

            .user__content__description a:hover {
                color: var(--theme__lighter);
            }

            .user__content__description img {
                max-width: 100%;
            }
        </style>
    </head>
    <body>
        <div class="main__user">
            <?=SetHeader()?>
            <div class="content">
                <div class="user">
                    <div class="user__panel">
                        <div class="user__panel__avatar -center">
                            <img src="uploads/avatars/<?=$target["avatar"]?>">
                        </div>
                        <div class="user__panel__username -center -title">
                            <?=$target["username"]?>
                        </div>
                        <div class="user__panel__details -subtitle">
                            Member type: <?=$memberType?><br>
                            Last seen: <?=TimeAgo($target["last_seen"])?><br>
                            Joined: <?=TimeAgo($target["time"])?>
                        </div>
                        <?php
                            if ($target["username"] != $user["username"]) {
                                $heartPlusIcon = Icon("heart_plus");
                                $flagIcon = Icon("flag");

                                echo <<<HTML
                                    <div class="user__panel__actions">
                                        <div class="user__panel__actions__follow user__panel__action -center">
                                            <button class="-button">
                                                {$heartPlusIcon}
                                            </button>
                                        </div>
                                        <div class="user__panel__actions__report user__panel__action -center">
                                            <button class="-button">
                                                {$flagIcon}
                                            </button>
                                        </div>
                                    </div>
                                HTML;
                            } else {
                                $settingsIcon = Icon("settings");

                                echo <<<HTML
                                    <div class="user__panel__settings -center -script__link" data-href="user/edit/">
                                        <button class="-button">
                                            {$settingsIcon}
                                        </button>
                                    </div>
                                HTML;
                            }
                        ?>
                    </div>
                    <div class="user__content">
                        <div class="user__content__description">
                            <?=$Parsedown->text($target["description"])?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>

    </script>
</html>