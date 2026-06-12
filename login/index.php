<?php

chdir("../");
require_once "common.php";
$user = getUser();

if ($user != false) {
    header("Location: ../");
}

?>

<html>
    <head>
        <title>
            Login | SaucePls
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
                        display: grid;
                        grid-template-columns: 1fr max-content 1fr;">
                        <div></div>
                        <div style="
                            padding: 5rem;">
                            <form style="
                                padding: 1rem;
                                background-color: #222;
                                border-radius: 1rem;
                                width: 20rem;"
                                action="server.php"
                                method="post"
                                enctype="multipart/form-data">
                                <div style="
                                    padding: 1rem;
                                    text-align: center;
                                    font-size: 1.5rem;
                                    font-weight: bold;">
                                    Login
                                </div>
                                <div style="
                                    padding: 1rem;
                                    font-size: 0.7rem;
                                    text-align: center;
                                    color: #aaa;">
                                    No passwords needed. A code will be sent to your email to confirm your login.
                                </div>
                                <div style="
                                    padding: 1rem;">
                                    Enter your email:
                                </div>
                                <div style="
                                    padding: 1rem;
                                    padding-top: 0rem;">
                                    <input type="email"
                                        name="email"
                                        placeholder="Email"
                                        required>
                                </div>
                                <div style="
                                    padding: 1rem;
                                    text-align: center;">
                                    <button name="method" value="login">
                                        <div style="
                                            display: grid;
                                            grid-template-columns: max-content 1fr;">
                                            <div style="
                                                display: flex;
                                                align-items: center;">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z"/></svg>
                                            </div>
                                            <div style="
                                                display: flex;
                                                align-items: center;
                                                padding-left: 0.5rem;">
                                                Submit
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div></div>
                    </div>
                    <div style="
                        background-color: #222;
                        border-left: 1px solid #555;
                        width: 15rem;">
                        <div style="
                            padding: 1rem;
                            font-size: 1.5rem;
                            text-align: center;">
                            More Sign-In Options
                        </div>
                        <div style="
                            padding: 1rem;
                            font-size: 0.7rem;
                            color: #aaa;
                            text-align: center;">
                            This feature is still in development
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