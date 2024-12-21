<?php

chdir("../../");
include("common.php");
Debug();
$data = GetSiteData();
$user = GetUserData();

if ($user == false) {
    Alert("You are not logged in.");
}

?>

<html>
    <head>
        <title>
            Edit | SaucePls
        </title>
        <base href="../../">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .content {
                padding: 5rem;
            }

            .title {
                padding: 1rem;
            }

            .edit {
                display: grid;
                grid-template-columns: 1fr 2fr;
            }

            .edit__info {
                padding: 1rem;
            }

            .edit__info__box {
                display: grid;
                grid-template-rows: max-content 1fr max-content;
                padding: 1rem;
                height: 40rem;
                background-color: #222;
                border-radius: 1rem;
            }

            .edit__info__box__avatar {
                display: grid;
                grid-template-columns: 1fr repeat(2, max-content);
                padding: 1rem;
            }

            .edit__info__box__avatar__label {
                padding: 1rem;
            }

            .edit__info__box__avatar__upload {
                padding: 1rem;
            }

            .edit__info__box__avatar__preview {
                padding: 1rem;
            }

            .edit__info__box__avatar__preview > img {
                width: 10rem;
                height: 10rem;
                object-fit: cover;
                border-radius: 1rem;
            }

            .edit__info__box__username {
                padding: 1rem;
            }

            .edit__info__box__username__label {
                padding: 1rem;
            }

            .edit__info__box__username__input {
                padding: 1rem;
            }

            .edit__info__box__actions {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
            }

            .edit__info__box__action {
                padding: 1rem;
            }

            .edit__description {
                padding: 1rem;
            }

            .edit__description__box {
                display: grid;
                grid-template-rows: max-content 1fr max-content;
                padding: 1rem;
                height: 40rem;
                background-color: #222;
                border-radius: 1rem;
            }

            .edit__description__box__label {
                padding: 1rem;
            }

            .edit__description__box__input {
                padding: 1rem;
            }

            .edit__description__box__input > textarea {
                height: 100%;
                resize: none;
            }

            .edit__description__box__note {
                display: grid;
                grid-template-columns: 1fr max-content;
            }

            .edit__description__box__note__text {
                padding: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="main__user__edit">
            <?=SetHeader()?>
            <div class="content">
                <div class="title -center -title">
                    Edit Profile
                </div>
                <form class="-form edit" action="server.php" method="post" enctype="multipart/form-data">
                    <div class="edit__info">
                        <div class="edit__info__box">
                            <div class="edit__info__box__avatar">
                                <div class="edit__info__box__avatar__label -center__flex">
                                    Avatar:
                                </div>
                                <div class="edit__info__box__avatar__upload -center__flex">
                                    <button class="-button" type="button" onclick="btnUpload(this)">
                                        <?=Icon("upload")?>
                                    </button>
                                    <input type="file" name="avatar" accept="image/*" style="display: none;">
                                </div>
                                <div class="edit__info__box__avatar__preview -center__flex">
                                    <img src="uploads/avatars/<?=$user["avatar"]?>">
                                </div>
                            </div>
                            <div class="edit__info__box__username">
                                <div class="edit__info__box__username__label">
                                    Username:
                                </div>
                                <div class="edit__info__box__username__input">
                                    <input class="-input -script__alphanum" value="<?=$user["username"]?>" name="username" placeholder="Username..." maxlength="20" required>
                                </div>
                            </div>
                            <div class="edit__info__box__actions -center">
                                <div class="edit__info__box__actions__cancel -edit__info__box__action -center">
                                    <button class="-button" type="button" onclick="btnCancel(this)">
                                        Cancel
                                    </button>
                                </div>
                                <div class="edit__info__box__actions__save -edit__info__box__action -center">
                                    <button class="-button" name="method" value="editProfile">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="edit__description">
                        <div class="edit__description__box">
                            <div class="edit__description__box__label">
                                Description:
                            </div>
                            <div class="edit__description__box__input">
                                <textarea class="-textarea" name="description" placeholder="Description..." maxlength="5000"><?=htmlentities($user["description"])?></textarea>
                            </div>
                            <div class="edit__description__box__note">
                                <div></div>
                                <div class="edit__description__box__note__text -subtitle">
                                    Markdown supported
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>
        function btnUpload(element) {
            let input = document.querySelector(".edit__info__box__avatar__upload > input");
            input.click();

            input.addEventListener("change", () => {
                let preview = document.querySelector(".edit__info__box__avatar__preview > img");
                preview.src = URL.createObjectURL(input.files[0]);
            });
        }

        function btnCancel(element) {
            if (confirm("Are you sure you want to cancel?")) {
                window.history.back();
            }
        }
    </script>
</html>