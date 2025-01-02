<?php

chdir("../");
include("common.php");
Debug();
$data = GetSiteData();
$user = GetUserData();
$requestIndex = FindIndex($data["requests"], "id", $_GET["id"]);

if ($requestIndex == -1) {
    Alert("The request does not exist.");
}

$request = $data["requests"][$requestIndex];

$requestTags = array_values(array_filter($data["requestTags"], function ($tag) use ($request) {
    return $tag["requestId"] == $request["id"];
}));

$tags = [];

foreach ($requestTags as $requestTag) {
    $tagIndex = FindIndex($data["tags"], "id", $requestTag["tagId"]);

    if ($tagIndex == -1) {
        continue;
    }

    $tag = $data["tags"][$tagIndex];
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