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
            
        </style>
    </head>
    <body>
        <div style="
            display: grid;
            grid-template-columns: max-content 1fr;
            height: 100%;
            box-sizing: border-box;
            overflow: hidden;">
            <?= renderNavigation() ?>
            <div style="
                display: grid;
                grid-template-rows: max-content 1fr;
                overflow: hidden;">
                <?= renderHeader() ?>
                <form style="
                    display: grid;
                    grid-template-columns: 1fr max-content;
                    overflow: hidden;"
                    action="server.php"
                    method="post"
                    enctype="multipart/form-data">
                    <div style="
                        overflow: auto;">
                        <div style="
                            padding: 1rem;
                            font-size: 1.5rem;">
                            Edit Profile
                        </div>
                        <div style="
                            display: grid;
                            grid-template-columns: repeat(2, 1fr);">
                            <div>
                                <div style="
                                    padding: 1rem;
                                    text-align: center;">
                                    Avatar
                                </div>
                                <div style="
                                    padding: 1rem;
                                    padding-top: 0rem;
                                    text-align: center;">
                                    <img style="
                                        width: 10rem;
                                        height: 10rem;
                                        border-radius: 50%;
                                        object-fit: cover;
                                        cursor: pointer;"
                                        src="uploads/avatars/<?= $user["avatar"] ?>"
                                        id="imgAvatar">
                                    <input style="
                                        display: none;"
                                        type="file"
                                        name="avatar"
                                        accept="image/*"
                                        id="inputAvatar">
                                </div>
                                <div style="
                                    padding: 1rem;
                                    padding-top: 0rem;
                                    text-align: center;
                                    font-size: 0.7rem;
                                    color: #aaa;">
                                    Click on the image to change
                                </div>
                            </div>
                            <div style="
                                display: grid;
                                grid-template-rows: 1fr repeat(2, max-content);">
                                <div></div>
                                <div style="
                                    padding: 1rem;">
                                    Username
                                </div>
                                <div style="
                                    padding: 1rem;
                                    padding-top: 0rem;">
                                    <input name="username"
                                        value="<?= $user["username"] ?>"
                                        maxlength="20"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div style="
                            padding: 1rem;">
                            Description
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;">
                            <textarea name="description"><?= htmlentities($user["description"]) ?></textarea>
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;
                            font-size: 0.7rem;
                            color: #aaa;">
                            Markdown is supported
                        </div>
                    </div>
                    <div style="
                        background-color: #222;
                        border-left: 1px solid #555;
                        width: 15rem;">
                        <div style="
                            padding: 1rem;
                            text-align: center;">
                            <button name="method"
                                value="edit_profile">
                                <div style="
                                    display: grid;
                                    grid-template-columns: max-content 1fr;">
                                    <div style="
                                        display: flex;
                                        align-items: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h447q16 0 30.5 6t25.5 17l114 114q11 11 17 25.5t6 30.5v447q0 33-23.5 56.5T760-120H200Zm365-155q35-35 35-85t-35-85q-35-35-85-35t-85 35q-35 35-35 85t35 85q35 35 85 35t85-35ZM280-560h280q17 0 28.5-11.5T600-600v-80q0-17-11.5-28.5T560-720H280q-17 0-28.5 11.5T240-680v80q0 17 11.5 28.5T280-560Z"/></svg>
                                    </div>
                                    <div style="
                                        display: flex;
                                        align-items: center;
                                        padding-left: 0.5rem;">
                                        Save
                                    </div>
                                </div>
                            </button>
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;
                            text-align: center;">
                            <button style="
                                background-color: #555;"
                                type="button"
                                id="btnCancel">
                                <div style="
                                    display: grid;
                                    grid-template-columns: max-content 1fr;">
                                    <div style="
                                        display: flex;
                                        align-items: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z"/></svg>
                                    </div>
                                    <div style="
                                        display: flex;
                                        align-items: center;
                                        padding-left: 0.5rem;">
                                        Cancel
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script src="script.js"></script>
        <script>
            const imgAvatar = document.getElementById("imgAvatar");
            const inputAvatar = document.getElementById("inputAvatar");
            const btnCancel = document.getElementById("btnCancel");
            const originalAvatar = imgAvatar.src;
            
            imgAvatar.onclick = () => {
                inputAvatar.click();
            }

            inputAvatar.onchange = () => {
                if (inputAvatar.files.length == 0) {
                    imgAvatar.src = originalAvatar;
                    return;
                }

                imgAvatar.src = URL.createObjectURL(inputAvatar.files[0]);
            }

            btnCancel.onclick = () => {
                if (confirm("Are you sure you want to cancel?")) {
                    location.href = "user/?id=<?= $user["username"] ?>";
                }
            }
        </script>
    </body>
</html>