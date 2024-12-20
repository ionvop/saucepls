<?php

chdir("../");
include("common.php");
Debug();

?>

<html>
    <head>
        <title>
            Login | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .content {
                padding: 5rem;
            }

            .login {
                display: grid;
                grid-template-columns: 1fr max-content 1fr;
            }

            .login__box {
                padding: 1rem;
                background-color: #222;
                border-radius: 1rem;
            }

            .login__box__title {
                padding: 1rem;
            }

            .login__box__label {
                padding: 1rem;
            }

            .login__box__note {
                padding: 1rem;
            }

            .login__box__input {
                padding: 1rem;
            }

            .login__box__button {
                padding: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="main__login">
            <?=SetHeader()?>
            <div class="content">
                <div class="login">
                    <div></div>
                    <form class="-form login__box" action="server.php" method="post" enctype="multipart/form-data">
                        <div class="login__box__title -center -title">
                            Login / Register
                        </div>
                        <div class="login__box__label -center">
                            Enter your email:
                        </div>
                        <div class="login__box__note -center -subtitle">
                            A code will be sent to your email which will be<br>
                            used for logging in or for registering an account.
                        </div>
                        <div class="login__box__input">
                            <input class="-input" type="email" name="email" placeholder="Email..." required>
                        </div>
                        <div class="login__box__button -center">
                            <button class="-button" name="method" value="login">
                                Submit
                            </button>
                        </div>
                    </form>
                    <div></div>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>

    </script>
</html>