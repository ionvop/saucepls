<?php

include "common.php";
$db = new SQLite3("database.db");

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
            body > .main {
                & > .page {
                    & > .content {
                        & > .posts {
                            display: grid;
                            grid-template-columns: repeat(2, 1fr);
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
                <div class="content -pad">
                    <div class="welcome -pad -title -center">
                        Welcome to SaucePls
                    </div>
                    <div class="subtitle -pad -center">
                        Upload images, anime screenshots, or manga panels and let the community help you find the source or artist!
                    </div>
                    <div class="posts">
                        <div class="trending -posts">
                            <div class="item">
                                <div class="image">
                                    <img src="assets/image.png">
                                </div>
                                <div class="content">
                                    <div class="title">
                                        <div class="text">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Maiores quia perferendis aliquid quae eveniet. Pariatur voluptatibus necessitatibus velit illum aperiam perferendis facere enim aut, quisquam dolorum veritatis a odit minus.
                                        </div>
                                    </div>
                                    <div class="tags">
                                        <div class="tag">
                                            <div class="box -subtitle">
                                                1girl
                                            </div>
                                        </div>
                                        <div class="tag">
                                            <div class="box -subtitle">
                                                blue_eyes
                                            </div>
                                        </div>
                                        <div class="tag">
                                            <div class="box -subtitle">
                                                blonde_hair
                                            </div>
                                        </div>
                                        <div class="tag">
                                            <div class="box -subtitle">
                                                1girl
                                            </div>
                                        </div>
                                        <div class="tag">
                                            <div class="box -subtitle">
                                                blue_eyes
                                            </div>
                                        </div>
                                        <div class="tag">
                                            <div class="box -subtitle">
                                                blonde_hair
                                            </div>
                                        </div>
                                        <div class="tag">
                                            <div class="box -subtitle">
                                                1girl
                                            </div>
                                        </div>
                                        <div class="tag">
                                            <div class="box -subtitle">
                                                blue_eyes
                                            </div>
                                        </div>
                                        <div class="tag">
                                            <div class="box -subtitle">
                                                blonde_hair
                                            </div>
                                        </div>
                                    </div>
                                    <div class="description">
                                        <div class="text">
                                            Lorem ipsum dolor, sit amet consectetur adipisicing elit. Consequatur ad, aperiam corporis a, modi explicabo perspiciatis, illum neque esse nisi delectus error! Commodi consectetur reiciendis autem, et sed totam eum?
                                        </div>
                                    </div>
                                    <div class="details -subtitle">
                                        1 day ago
                                    </div>
                                </div>
                                <div class="status -center__flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="recent">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="script.js"></script>
        <script>

        </script>
    </body>
</html>