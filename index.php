<?php

require_once "common.php";
$user = getUser();
$db = new SQLite3("database.db");

?>

<html>
    <head>
        <title>
            Home | SaucePls
        </title>
        <base href="./">
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
                    <div style="
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        padding-top: 1rem;
                        padding-left: 1rem;">
                        <?php
                            $query = <<<SQL
                                SELECT * FROM `posts` LIMIT 10
                            SQL;

                            $posts = $db->query($query);

                            while ($post = $posts->fetchArray(SQLITE3_ASSOC)) {
                                $query = <<<SQL
                                    SELECT * FROM `users` WHERE `id` = :id
                                SQL;

                                $stmt = $db->prepare($query);
                                $stmt->bindValue(":id", $post["user_id"]);
                                $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
                                $post["title"] = htmlentities($post["title"]);
                                $timeAgo = timeAgo($post["time"]);

                                $query = <<<SQL
                                    SELECT * FROM `post_tags` WHERE `post_id` = :id
                                SQL;

                                $stmt = $db->prepare($query);
                                $stmt->bindValue(":id", $post["id"]);
                                $tags = $stmt->execute();
                                $tagString = "";

                                while ($tag = $tags->fetchArray(SQLITE3_ASSOC)) {
                                    $query = <<<SQL
                                        SELECT * FROM `tags` WHERE `id` = :id
                                    SQL;

                                    $stmt = $db->prepare($query);
                                    $stmt->bindValue(":id", $tag["tag_id"]);
                                    $tag = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
                                    $tagString .= $tag["name"] . " ";
                                }
                                
                                echo <<<HTML
                                    <div style="
                                        padding: 1rem;
                                        padding-top: 0rem;
                                        padding-left: 0rem;">
                                        <a style="
                                            display: block;
                                            background-color: #222;
                                            border-radius: 1rem;"
                                            href="post/{$post['id']}">
                                            <div style="
                                                display: grid;
                                                grid-template-columns: max-content 1fr max-content;">
                                                <div style="
                                                    display: flex;
                                                    align-items: center;
                                                    padding: 1rem;">
                                                    <img style="
                                                        width: 2rem;
                                                        height: 2rem;
                                                        border-radius: 50%;
                                                        object-fit: cover;"
                                                        src="uploads/avatars/{$user['avatar']}">
                                                </div>
                                                <div style="
                                                    display: flex;
                                                    align-items: center;
                                                    padding: 1rem;
                                                    padding-left: 0rem;">
                                                    {$user["username"]}
                                                </div>
                                                <div style="
                                                    display: flex;
                                                    align-items: center;
                                                    padding: 1rem;
                                                    padding-left: 0rem;
                                                    font-size: 0.7rem;
                                                    color: #aaa;"
                                                    data-timestamp="{$post['time']}">
                                                    {$timeAgo}
                                                </div>
                                            </div>
                                            <div style="
                                                padding: 1rem;
                                                padding-top: 0rem;">
                                                {$post["title"]}
                                            </div>
                                            <div style="
                                                padding: 1rem;
                                                padding-top: 0rem;">
                                                <img style="
                                                    width: 100%;
                                                    object-fit: cover;
                                                    aspect-ratio: 1/1;"
                                                    src="uploads/posts/{$post['image']}">
                                            </div>
                                            <div style="
                                                display: -webkit-box;
                                                -webkit-box-orient: vertical;
                                                -webkit-line-clamp: 2;
                                                overflow: hidden;
                                                padding: 1rem;
                                                padding-top: 0rem;
                                                font-size: 0.7rem;
                                                color: #aaa;">
                                                {$tagString}
                                            </div>
                                        </a>
                                    </div>
                                HTML;
                            }
                        ?>
                    </div>
                    <form style="
                        background-color: #222;
                        border-left: 1px solid #555;
                        width: 15rem;">
                        <?php
                            if ($user != false) {
                                echo <<<HTML
                                    <div style="
                                        padding: 1rem;
                                        text-align: center;
                                        border-bottom: 1px solid #555;">
                                        <a href="upload/">
                                            <button type="button">
                                                <div style="
                                                    display: grid;
                                                    grid-template-columns: max-content 1fr;">
                                                    <div style="
                                                        display: flex;
                                                        align-items: center;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M440-440H240q-17 0-28.5-11.5T200-480q0-17 11.5-28.5T240-520h200v-200q0-17 11.5-28.5T480-760q17 0 28.5 11.5T520-720v200h200q17 0 28.5 11.5T760-480q0 17-11.5 28.5T720-440H520v200q0 17-11.5 28.5T480-200q-17 0-28.5-11.5T440-240v-200Z"/></svg>
                                                    </div>
                                                    <div style="
                                                        display: flex;
                                                        align-items: center;
                                                        padding-left: 0.5rem;">
                                                        New Request
                                                    </div>
                                                </div>
                                            </button>
                                        </a>
                                    </div>
                                HTML;
                            }
                        ?>
                        <div style="
                            padding: 1rem;
                            text-align: center;
                            font-size: 1.5rem;">
                            Search
                        </div>
                        <div style="
                            display: grid;
                            grid-template-columns: max-content 1fr;">
                            <div style="
                                display: flex;
                                align-items: center;
                                padding: 1rem;">
                                Sort:
                            </div>
                            <div style="
                                display: flex;
                                align-items: center;
                                padding: 1rem;
                                padding-left: 0rem;">
                                <select name="sort">
                                    <option value="recent">
                                        Recent
                                    </option>
                                    <option value="trending">
                                        Trending
                                    </option>
                                    <option value="follow">
                                        Popular
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div style="
                            padding: 1rem;">
                            <input type="text"
                                name="q"
                                placeholder="Keywords or tags..."
                                id="g_inputTags">
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;">
                            <div style="
                                display: none;
                                background-color: #111;
                                border-radius: 1rem;
                                overflow: hidden;"
                                id="g_panelSuggestions">

                            </div>
                        </div>
                        <div style="
                            padding: 1rem;
                            text-align: center;">
                            <button>
                                <div style="
                                    display: grid;
                                    grid-template-columns: max-content 1fr;">
                                    <div style="
                                        display: flex;
                                        align-items: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M380-320q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l224 224q11 11 11 28t-11 28q-11 11-28 11t-28-11L532-372q-30 24-69 38t-83 14Zm0-80q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>
                                    </div>
                                    <div style="
                                        display: flex;
                                        align-items: center;
                                        padding-left: 0.5rem;">
                                        Search
                                    </div>
                                </div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>
        
    </script>
</html>