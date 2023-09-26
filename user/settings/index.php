<?php

chdir("../../");
include("common.php");
Debug();
$data = GetSiteData();
$userIndex = FindIndexByKeyValue($data->users, "id", AuthenticateUser());

if ($userIndex == -1) {
    Alert("No strangers allowed");
}

$user = $data->users[$userIndex];

?>

<html>
    <head>
        <title>
            Settings | SaucePls
        </title>
        <base href="../../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .main__user__settings {
                padding: 5rem;
            }

            .-form-panel {
                margin-top: 5rem;
            }

            label {
                display: block;
                font-size: 2rem;
            }

            .-input {
                margin-top: 1rem;
                width: 80%;
            }

            .-textarea {
                margin-top: 1rem;
                width: 80%;
                height: 10rem;
            }

            .avatar__preview {
                margin-top: 1rem;
            }

            .avatar__preview img {
                width: 15rem;
                height: 15rem;
                border-radius: 1rem;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <div class="main__user__settings">
            <a href="./">
                <div class="-title -center">
                    SaucePls
                </div>
            </a>
            <div class="-form-panel -center">
                <form action="server.php" method="post" enctype="multipart/form-data">
                    <div class="-title">
                        Settings
                    </div>
                    <div class="-form-panel__section">
                        <label for="avatar">
                            Avatar:
                        </label>
                        <input type="file" name="avatar" accept=".jpg, .png" onchange="UpdatePreview()" style="display: none;" class="-input">
                        <div class="avatar__preview">
                            <img src="uploads/avatar/<?=$user->avatar?>" onclick="btnUpload()">
                        </div>
                    </div>
                    <div class="-form-panel__section">
                        <label for="username">
                            Username:
                        </label>
                        <input name="username" value="<?=htmlentities($user->username)?>" class="-input">
                    </div>
                    <div class="-form-panel__section">
                        <label for="description">
                            Description:
                        </label>
                        <textarea name="description" class="-textarea -center--block"><?=htmlentities($user->description)?></textarea>
                    </div>
                    <div class="-form-panel__section">
                        <label for="email">
                            Email:
                        </label>
                        <input type="email" name="email" value="<?=htmlentities($user->email)?>" class="-input">
                    </div>
                    <div class="-form-panel__section--separator">
                        <div class="-title2">
                            Change Password
                        </div>
                    </div>
                    <div class="-form-panel__section">
                        <label for="password">
                            Password:
                        </label>
                        <input type="password" name="password" class="-input">
                    </div>
                    <div class="-form-panel__section">
                        <label for="newPassword">
                            Change password:
                        </label>
                        <input type="password" name="newPassword" class="-input">
                    </div>
                    <div class="-form-panel__section">
                        <label for="repassword">
                            Confirm password:
                        </label>
                        <input type="password" name="repassword" class="-input">
                    </div>
                    <div class="-form-panel__section">
                        <button name="method" value="updateProfile" class="-button">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>
        function btnUpload() {
            q("input[name=\"avatar\"]").click();
        }

        function UpdatePreview() {
            let output = q(".avatar__preview img");
            output.src = URL.createObjectURL(event.target.files[0]);

            output.onload = () => {
                URL.revokeObjectURL(output.src) // free memory
            }
        }
    </script>
</html>