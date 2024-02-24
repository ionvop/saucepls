<?php

chdir("../../");
include("common.php");
Debug();

$user = GetUserData();

if ($user == false) {
    Alert("Session expired.");
}

?>

<html>
    <head>
        <base href="../../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .content {
                padding: 5rem;
            }

            .content__title {
                padding: 1rem;
                font-size: 3rem;
                user-select: none;
                cursor: pointer;
            }

            .-form {
                width: 50%;
                margin-top: 3rem;
            }

            .input__avatar > img {
                width: 20rem;
                height: 20rem;
                border-radius: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="main__user__edit">
            <div class="content">
                <div class="content__title -center" onclick="btnTitle(this)">
                    SaucePls
                </div>
                <div class="-form -center--block">
                    <div class="-form__title -center">
                        Edit
                    </div>
                    <div class="-form__field">
                        <div class="-form__field__label">
                            Avatar:
                        </div>
                        <div class="-form__field__input input__avatar">
                            <img src="uploads/avatar/<?=htmlentities($user["avatar"])?>" onclick="btnAvatar(this)">
                        </div>
                    </div>
                    <div class="-form__field">
                        <div class="-form__field__label">
                            Username:
                        </div>
                        <div class="-form__field__input">
                            <input class="-input" name="username" value="<?=htmlentities($user["username"])?>">
                        </div>
                    </div>
                    <div class="-form__field">
                        <div class="-form__field__label">
                            Description:
                        </div>
                        <div class="-form__field__input">
                            <textarea class="-textarea" name="description"><?=htmlentities($user["description"])?></textarea>
                        </div>
                    </div>
                    <div class="-form__field">
                        <div class="-form__field__label">
                            Email:
                        </div>
                        <div class="-form__field__input">
                            <input class="-input" name="email" value="<?=htmlentities($user["email"])?>">
                        </div>
                    </div>
                    <div class="-form__title -center">
                        Change password
                    </div>
                    <div class="-form__field">
                        <div class="-form__field__label">
                            Password:
                        </div>
                        <div class="-form__field__input">
                            <input type="password" class="-input" name="password">
                        </div>
                    </div>
                    <div class="-form__field">
                        <div class="-form__field__label">
                            New password:
                        </div>
                        <div class="-form__field__input">
                            <input type="password" class="-input" name="newpassword">
                        </div>
                    </div>
                    <div class="-form__field">
                        <div class="-form__field__label">
                            Confirm password:
                        </div>
                        <div class="-form__field__input">
                            <input type="password" class="-input" name="repassword">
                        </div>
                    </div>
                    <div class="-form__submit">
                        <div class="-form__submit__button -center">
                            <button class="-button" onclick="btnSave(this)">
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form action="server.php" method="post" enctype="multipart/form-data" style="display: none;">
            <input type="file" accept="image/*" name="avatar">
            <input name="username">
            <textarea name="description"></textarea>
            <input type="email" name="email">
            <input type="password" name="password">
            <input type="password" name="newpassword">
            <input type="password" name="repassword">
            <input name="method" value="edit_profile">
        </form>
    </body>
    <script src="script.js"></script>
    <script>
        function btnTitle(element) {
            location.href = "./";
        }

        function btnAvatar(element) {
            document.querySelector("body > form > input[name='avatar']").click();

            document.querySelector("body > form > input[name='avatar']").onchange = () => {
                document.querySelector(".input__avatar > img").src = URL.createObjectURL(event.target.files[0]);

                document.querySelector(".input__avatar > img").onload = () => {
                    URL.revokeObjectURL(document.querySelector(".input__avatar > img").src);
                }
            }
        }

        function btnSave(element) {
            document.querySelector("body > form > input[name='username']").value = document.querySelector(".-form input[name='username']").value;
            document.querySelector("body > form > textarea[name='description']").value = document.querySelector(".-form textarea[name='description']").value;
            document.querySelector("body > form > input[name='email']").value = document.querySelector(".-form input[name='email']").value;
            document.querySelector("body > form > input[name='password']").value = document.querySelector(".-form input[name='password']").value;
            document.querySelector("body > form > input[name='newpassword']").value = document.querySelector(".-form input[name='newpassword']").value;
            document.querySelector("body > form > input[name='repassword']").value = document.querySelector(".-form input[name='repassword']").value;
            document.querySelector("body > form").submit();
        }
    </script>
</html>