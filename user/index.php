<?php

chdir("../");
include "common.php";
$user = getUser();
$target = getUserByName($_GET["id"]);
$comments = getUserComments($target["id"], $_GET["max"] ?? 10);

?>

<html>
    <head>
        <title>
            <?=$target["username"]?> | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body > .main {
                & > .page {
                    & > .content {
                        display: grid;
                        grid-template-columns: 1fr 2fr;

                        & > .profile {
                            border-right: 1px solid #555;
                            background-color: #222;

                            & > .avatar {
                                position: relative;

                                & > img {
                                    width: 10rem;
                                    height: 10rem;
                                    object-fit: cover;
                                    border-radius: 50%;
                                }

                                & > .edit {
                                    display: grid;
                                    grid-template-columns: 1fr max-content;
                                    position: absolute;
                                    top: 0rem;
                                    left: 0rem;
                                    right: 0rem;
                                }
                            }

                            & > .details {
                                line-height: 2rem;  
                            }

                            & > .description {
                                & > .box {
                                    background-color: #111;
                                    border-radius: 1rem;
                                }
                            }
                        }

                        & > .panel {
                            & > .comments {
                                & > .comment {
                                    display: grid;
                                    grid-template-columns: max-content 1fr max-content;
                                    border-bottom: 1px solid #555;

                                    & > .avatar {
                                        & > img {
                                            width: 3rem;
                                            height: 3rem;
                                            object-fit: cover;
                                            border-radius: 50%;
                                        }
                                    }

                                    & > .input {
                                        & > textarea {
                                            height: 5rem;
                                        }
                                    }

                                    & > .send {
                                        display: grid;
                                        grid-template-rows: 1fr max-content;
                                    }
                                }
                            }
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
                <div class="content">
                    <div class="profile -pad">
                        <div class="avatar -pad -center">
                            <img src="uploads/avatars/<?=$target["avatar"]?>">
                            <?php
                                if ($user["id"] == $target["id"]) {
                                    echo <<<HTML
                                        <div class="edit">
                                            <div></div>
                                            <a href="user/edit/" class="-a button -pad">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M160-120q-17 0-28.5-11.5T120-160v-97q0-16 6-30.5t17-25.5l505-504q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L313-143q-11 11-25.5 17t-30.5 6h-97Zm544-528 56-56-56-56-56 56 56 56Z"/></svg>
                                            </a>
                                        </div>
                                    HTML;
                                }
                            ?>
                        </div>
                        <div class="username -pad -title -center">
                            <?=$target["username"]?>
                        </div>
                        <div class="details -pad -subtitle -center">
                            Last seen: <?=timeAgo($target["last_seen"])?> &nbsp; | &nbsp; Joined: <?=timeAgo($target["time"])?><br>
                            Following: 0 &nbsp; | &nbsp; Followers: 0
                        </div>
                        <div class="description -pad">
                            <div class="box -pad">
                                <?=nl2br(htmlentities($target["description"]))?>
                            </div>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="comments">
                            <div class="title -pad -title">
                                Comments
                            </div>
                            <form action="server.php" class="-form comment" method="post" enctype="multipart/form-data">
                                <div class="avatar -pad">
                                    <img src="uploads/avatars/<?=$user["avatar"]?>">
                                </div>
                                <div class="input -pad">
                                    <textarea name="comment" class="-textarea" placeholder="Write a comment..."></textarea>
                                </div>
                                <div class="send">
                                    <div></div>
                                    <div class="button -pad">
                                        <button class="-button" name="method" value="user_comment">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M176-183q-20 8-38-3.5T120-220v-180l320-80-320-80v-180q0-22 18-33.5t38-3.5l616 260q25 11 25 37t-25 37L176-183Z"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="target_name" value="<?=$target["username"]?>">
                            </form>
                            <div class="render -comments">
                                <?php
                                    foreach ($comments as $comment) {
                                        echo renderComment($comment);
                                    }
                                ?>
                            </div>
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