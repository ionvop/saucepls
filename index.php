<?php

include("common.php");
Debug();
$data = GetSiteData();
$recentPosts = SortArray($data["posts"], "time", true);

?>

<html>
    <head>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .popular {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .popular__likes__title {
                padding: 1rem;
                font-size: 2rem;
            }

            .popular__trending__title {
                padding: 1rem;
                font-size: 2rem;
            }

            .recent__title {
                padding: 1rem;
                font-size: 2rem;
            }
        </style>
    </head>
    <body>
        <div class="main">
            <?=SetHeader()?>
            <div class="popular">
                <div class="popular__likes">
                    <div class="popular__likes__title -center">
                        Popular unsolved:
                    </div>
                    <div class="-posts__render">
                        <?php
                            foreach ($recentPosts as $key => $value) {
                                echo RenderItem($value);
                            }
                        ?>
                    </div>
                    <div class="popular__likes__more -center">
                        <button class="-button">
                            Show more
                        </button>
                    </div>
                </div>
                <div class="popular__trending">
                    <div class="popular__trending__title -center">
                        Trending unsolved:
                    </div>
                    <div class="-posts__render">
                        <?php
                            foreach ($recentPosts as $key => $value) {
                                echo RenderItem($value);
                            }
                        ?>
                    </div>
                    <div class="popular__trending__more -center">
                        <button class="-button">
                            Show more
                        </button>
                    </div>
                </div>
            </div>
            <div class="recent">
                <div class="recent__title -center">
                    Recent posts:
                </div>
                <div class="-posts__render">
                    <?php
                        foreach ($recentPosts as $key => $value) {
                            echo RenderItem($value);
                        }
                    ?>
                </div>
                <div class="recent__more -center">
                    <button class="-button">
                        Show more
                    </button>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>

    </script>
</html>