<?php

chdir("../");
require_once "common.php";
$user = getUser();

if (isset($_GET["id"]) == false) {
    alert("Invalid user.");
}

$target = getUserByName($_GET["id"]);

if ($target == false) {
    alert("Invalid user.");
}

$roleMap = [
    "member" => "Member",
    "admin" => "Admin"
];

?>

<html>
    <head>
        <title>
            User | SaucePls
        </title>
        <base href="../">
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
                        overflow: auto;">
                        <div style="
                            padding: 1rem;
                            font-size: 1.5rem;">
                            About this user
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;
                            border-bottom: 1px solid #555;"
                            id="panelDescription">
                            <?= htmlentities($target["description"]) ?>
                        </div>
                    </div>
                    <div style="
                        background-color: #222;
                        border-left: 1px solid #555;
                        width: 15rem;">
                        <div style="
                            padding: 1rem;
                            text-align: center;">
                            <img style="
                                width: 10rem;
                                height: 10rem;
                                border-radius: 50%;
                                object-fit: cover;"
                                src="uploads/avatars/<?= $target["avatar"] ?>">
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;
                            text-align: center;
                            font-size: 1.5rem;">
                            <?= $target["username"] ?>
                        </div>
                        <div style="
                            padding: 1rem;
                            padding-top: 0rem;
                            text-align: center;
                            font-size: 0.7rem;
                            color: #aaa;
                            line-height: 1.5rem;">
                            <?= $roleMap[$target["role"]] ?><br>
                            Following: 0 &nbsp; | &nbsp; Followers: 0<br>
                            Last seen: <span data-timestamp="<?= $target["last_seen"] ?>"><?= timeAgo($target["last_seen"]) ?></span><br>
                            Joined: <span data-timestamp="<?= $target["time"] ?>"><?= timeAgo($target["time"]) ?></span>
                        </div>
                        <?php
                            $button = <<<HTML
                                <form style="
                                    padding: 1rem;
                                    text-align: center;"
                                    action="server.php"
                                    method="post"
                                    enctype="multipart/form-data">
                                    <button name="method"
                                        value="follow">
                                        <div style="
                                            display: grid;
                                            grid-template-columns: max-content 1fr;">
                                            <div style="
                                                display: flex;
                                                align-items: center;">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M720-520h-80q-17 0-28.5-11.5T600-560q0-17 11.5-28.5T640-600h80v-80q0-17 11.5-28.5T760-720q17 0 28.5 11.5T800-680v80h80q17 0 28.5 11.5T920-560q0 17-11.5 28.5T880-520h-80v80q0 17-11.5 28.5T760-400q-17 0-28.5-11.5T720-440v-80Zm-473-7q-47-47-47-113t47-113q47-47 113-47t113 47q47 47 47 113t-47 113q-47 47-113 47t-113-47ZM40-240v-32q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v32q0 33-23.5 56.5T600-160H120q-33 0-56.5-23.5T40-240Z"/></svg>
                                            </div>
                                            <div style="
                                                display: flex;
                                                align-items: center;
                                                padding-left: 0.5rem;">
                                                Follow
                                            </div>
                                        </div>
                                    </button>
                                </form>
                            HTML;

                            if ($user != false) {
                                $button = <<<HTML
                                    <div style="
                                        display: block;
                                        padding: 1rem;
                                        text-align: center;">
                                        <a href="user/edit/">
                                            <button>
                                                <div style="
                                                    display: grid;
                                                    grid-template-columns: max-content 1fr;">
                                                    <div style="
                                                        display: flex;
                                                        align-items: center;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M433-80q-27 0-46.5-18T363-142l-9-66q-13-5-24.5-12T307-235l-62 26q-25 11-50 2t-39-32l-47-82q-14-23-8-49t27-43l53-40q-1-7-1-13.5v-27q0-6.5 1-13.5l-53-40q-21-17-27-43t8-49l47-82q14-23 39-32t50 2l62 26q11-8 23-15t24-12l9-66q4-26 23.5-44t46.5-18h94q27 0 46.5 18t23.5 44l9 66q13 5 24.5 12t22.5 15l62-26q25-11 50-2t39 32l47 82q14 23 8 49t-27 43l-53 40q1 7 1 13.5v27q0 6.5-2 13.5l53 40q21 17 27 43t-8 49l-48 82q-14 23-39 32t-50-2l-60-26q-11 8-23 15t-24 12l-9 66q-4 26-23.5 44T527-80h-94Zm49-260q58 0 99-41t41-99q0-58-41-99t-99-41q-59 0-99.5 41T342-480q0 58 40.5 99t99.5 41Z"/></svg>
                                                    </div>
                                                    <div style="
                                                        display: flex;
                                                        align-items: center;
                                                        padding-left: 0.5rem;">
                                                        Edit Profile
                                                    </div>
                                                </div>
                                            </button>
                                        </a>
                                    </div>
                                HTML;
                            }

                            echo $button;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/marked/lib/marked.umd.js"></script>
        <script src="script.js"></script>
        <script>
            const panelDescription = document.getElementById("panel-description");

            (() => {
                panelDescription.innerHTML = marked.parse(panelDescription.innerHTML);
            })()
        </script>
    </body>
</html>