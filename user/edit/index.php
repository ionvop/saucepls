<?php

chdir("../../");
include "common.php";
$user = getUser();

?>

<html>
    <head>
        <title>
            Edit Profile | SaucePls
        </title>
        <base href="../../">
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

                                & > .back {
                                    display: grid;
                                    grid-template-columns: max-content 1fr;
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

                        & > .edit {
                            overflow: auto;

                            & > .submit {
                                display: grid;
                                grid-template-columns: 1fr max-content;
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
                            <img src="uploads/avatars/<?=$user["avatar"]?>" id="preview">
                            <div class="back">
                                <a href="user/?id=<?=$user["username"]?>" class="-a button -pad">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m313-440 196 196q12 12 11.5 28T508-188q-12 11-28 11.5T452-188L188-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l264-264q11-11 27.5-11t28.5 11q12 12 12 28.5T508-715L313-520h447q17 0 28.5 11.5T800-480q0 17-11.5 28.5T760-440H313Z"/></svg>
                                </a>
                                <div></div>
                            </div>
                        </div>
                        <div class="username -pad -title -center">
                            <?=$user["username"]?>
                        </div>
                        <div class="details -pad -subtitle -center">
                            Last seen: <?=timeAgo($user["last_seen"])?> &nbsp; | &nbsp; Joined: <?=timeAgo($user["time"])?><br>
                            Following: 0 &nbsp; | &nbsp; Followers: 0
                        </div>
                        <div class="description -pad">
                            <div class="box -pad">
                                <?=nl2br(htmlentities($user["description"]))?>
                            </div>
                        </div>
                    </div>
                    <form action="server.php" class="-form edit" method="post" enctype="multipart/form-data">
                        <div class="username field -pad">
                            <div class="label -pad">
                                Username
                            </div>
                            <div class="input -pad">
                                <input class="-input" name="username" value="<?=$user["username"]?>" required>
                            </div>
                        </div>
                        <div class="avatar field -pad">
                            <div class="label -pad">
                                Avatar
                            </div>
                            <div class="input -pad">
                                <input type="file" name="avatar" accept="image/*" onchange="previewImage(event)">
                            </div>
                        </div>
                        <div class="description field -pad">
                            <div class="label -pad">
                                Description
                            </div>
                            <div class="input -pad">
                                <textarea class="-textarea" name="description"><?=htmlentities($user["description"])?></textarea>
                            </div>
                        </div>
                        <div class="submit -pad">
                            <div></div>
                            <div class="button -pad">
                                <button class="-button" name="method" value="edit_profile">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script src="script.js"></script>
        <script>
            function previewImage(event) {
                let reader = new FileReader();
                
                reader.onload = function() {
                    let output = document.getElementById('preview');
                    output.src = reader.result;
                
                    output.onload = function() {
                        URL.revokeObjectURL(output.src) // free memory
                    }
                }
                
                reader.readAsDataURL(event.target.files[0]);
            }
        </script>
    </body>
</html>