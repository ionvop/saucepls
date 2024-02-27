<?php

chdir("../");
include("common.php");

$data = GetSiteData();
$postIndex = FindIndex($data["posts"], "id", $_GET["id"]);

if ($postIndex == -1) {
    Alert("Post not found.");
}

$post = $data["posts"][$postIndex];

?>

<html>
    <head>
        <base href="../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .panel {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .panel__details__text {
                padding: 1rem;
            }

            .panel__details__text__label {
                padding: 1rem;
                font-size: 2rem;
            }

            .panel__details__text__content {
                padding: 1rem;
                background-color: #222;
                border-radius: 1rem;
                white-space: pre;
            }

            .panel__details__tags {
                padding: 1rem;
            }

            .panel__details__tags__label {
                font-size: 2rem;
                padding: 1rem;
            }

            .panel__details__tags__content {
                padding: 1rem;
                background-color: #222;
                border-radius: 1rem;
                white-space: pre;
            }

            .panel__preview__image {
                padding: 1rem;
            }

            .panel__preview__image > img {
                width: 100%;
                border-radius: 1rem;
            }

            .panel__preview__description__label {
                padding: 1rem;
                font-size: 2rem;
            }

            .panel__preview__description__content {
                padding: 1rem;
                background-color: #222;
                border-radius: 1rem;
                white-space: pre;
            }
        </style>
    </head>
    <body>
        <div class="main__post">
            <?=SetHeader()?>
            <div class="panel">
                <div class="panel__details">
                    <div class="panel__details__text">
                        <div class="panel__details__text__label">
                            Text:
                        </div>
                        <div class="panel__details__text__content"><?=htmlentities($post["text"])?></div>
                    </div>
                    <div class="panel__details__tags">
                        <div class="panel__details__tags__label">
                            Tags:
                        </div>
                        <div class="panel__details__tags__content"><?=htmlentities($post["tags"])?></div>
                    </div>
                </div>
                <div class="panel__preview">
                    <div class="panel__preview__image">
                        <img src="uploads/posts/<?=htmlentities($post["file"])?>">
                    </div>
                    <div class="panel__preview__description">
                        <div class="panel__preview__description__label">
                            Description:
                        </div>
                        <div class="panel__preview__description__content"><?=htmlentities($post["description"])?></div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script></script>
</html>