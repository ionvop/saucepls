<?php

?>

<html>
    <head>
        <title>
            Register | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .main__register {
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
            }

            .popup {
                height: 4rem;
                overflow: hidden;
                transition-duration: 1s;
            }

            a[href="login/"] {
                margin-top: 1rem;
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="main__register">
            <a href="./">
                <div class="-title -center">
                    SaucePls
                </div>
            </a>
            <div class="-form-panel -center">
                <form action="server.php" method="post" enctype="multipart/form-data">
                    <div class="-title">
                        Register
                    </div>
                    <div class="-form-panel__section">
                        <label for="username">
                            Username:
                        </label>
                        <input name="username" class="-input">
                    </div>
                    <div class="-form-panel__section">
                        <label for="password">
                            Password:
                        </label>
                        <div class="popup -subtitle">
                            Your password will be encrypted but we can't guarantee its security. Please use a password that you've never used in any other sites.
                        </div>
                        <input type="password" name="password" class="-input" onfocus="ShowPopup()" onfocusout="HidePopup()">
                    </div>
                    <div class="-form-panel__section">
                        <label for="repassword">
                            Confirm Password:
                        </label>
                        <input type="password" name="repassword" class="-input">
                    </div>
                    <div class="-form-panel__section">
                        <label for="email">
                            Email:
                        </label>
                        <input type="email" name="email" class="-input">
                    </div>
                    <div class="-form-panel__section">
                        <button name="method" value="register" class="-button">
                            Register
                        </button>
                    </div>
                    <a href="login/">
                        Login
                    </a>
                </form>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>
        q(".popup").style.transitionDuration = "0s";
        q(".popup").style.marginTop = "0rem";
        q(".popup").style.height = "0rem";

        function ShowPopup() {
            q(".popup").style.transitionDuration = "";
            q(".popup").style.marginTop = "1rem";
            q(".popup").style.height = "";
        }

        function HidePopup() {
            q(".popup").style.marginTop = "0rem";
            q(".popup").style.height = "0rem";
        }
    </script>
</html>