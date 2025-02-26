<?php

chdir("../");
include("common.php");
Debug();
$db = new SQLite3("database.db");
$user = GetUser();

$query = <<<SQL
    SELECT * FROM `requests` WHERE `id` = :id
SQL;

$stmt = $db->prepare($query);
$stmt->bindValue(":id", $_GET["id"]);
$request = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

$query = <<<SQL
    SELECT * FROM `request_tags` WHERE `request_id` = :request_id
SQL;

$stmt = $db->prepare($query);
$stmt->bindValue(":request_id", $request["id"]);
$requestTags = $stmt->execute();
$tags = [];

while ($requestTag = $requestTags->fetchArray(SQLITE3_ASSOC)) {
    $query = <<<SQL
        SELECT * FROM `tags` WHERE `id` = :id
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":id", $requestTag["tag_id"]);
    $tag = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    $tags[] = $tag["name"];
}

$title = "";
$i = 0;

foreach ($tags as $tag) {
    if ($i > 3) {
        break;
    }

    $title .= $tag . ", ";
    $i++;
}

$title = substr($title, 0, -2);

?>

<html>
    <head>
        <title>
            <?=$title?> | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
        
        </style>
    </head>
    <body>
        <div class="main__request">
            <?=SetHeader()?>
            <div class="content">
                <div class="request">
                    <div class="request__panel">
                        <div class="request__panel__tags">
                            <div class="request__panel__tags__title -title">
                                Tags
                            </div>
                            <div class="request__panel__tags__render">
                                <div class="item -script__link" data-href="search/?q=1girl">
                                    1girl
                                </div>
                            </div>
                        </div>
                        <div class="request__panel__details">
                            <div class="request__panel__details__author">
                                <div class="request__panel__details__author__avatar">
                                    <img src="uploads/avatars/default.jpg">
                                </div>
                                <div class="request__panel__details__author__username">
                                    ionvop
                                </div>
                            </div>
                        </div>
                        <div class="request__panel__actions">
                            <div class="request__panel__actions__bookmark">
                                <button class="-button">
                                    <?=Icon("bookmark_border")?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="request__content">
                        <div class="request__content__image">
                            <img src="uploads/requests/default.jpg">
                        </div>
                        <div class="request__content__text">
                            <div class="request__content__text__box">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Architecto, earum numquam officia perspiciatis, repudiandae natus pariatur, inventore sint possimus laborum cumque id neque voluptate quis. Ducimus laborum possimus porro praesentium?
                            </div>
                        </div>
                        <div class="request__content__description">
                            <div class="request__content__description__text">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Maiores molestiae labore atque maxime veniam ea voluptatem alias, esse perspiciatis accusamus amet provident rem ipsam laboriosam quam a pariatur quis possimus!
                            </div>
                            <div class="request__content__description__author">
                                <div class="request__content__description__author__avatar">
                                    <img src="uploads/avatars/default.jpg">
                                </div>
                                <div class="request__content__description__author__username">
                                    ionvop
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="responses">
                        <div class="responses__answers">
                            <div class="responses__answers__title">
                                Answers
                            </div>
                            <div class="responses__answers__render">
                                
                            </div>
                        </div>
                        <div class="responses__comments">
                            <div class="responses__comments__title">
                                Comments
                            </div>
                            <div class="responses__comments__render">

                            </div>
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