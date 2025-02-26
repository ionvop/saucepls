<?php

chdir("../");
include("common.php");
Debug();

?>

<html>
    <head>
        <title>
            Register | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .content {
                padding: 5rem;
            }

            .register {
                display: grid;
                grid-template-columns: 1fr max-content 1fr;
            }

            .register__box {
                padding: 1rem;
                background-color: #222;
                border-radius: 1rem;
            }

            .register__box__title {
                padding: 1rem;
            }

            .register__box__label {
                padding: 1rem;
            }

            .register__box__warning {
                padding: 1rem;
            }

            .register__box__input {
                padding: 1rem;
            }

            .register__box__button {
                padding: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="main__register">
            <?=SetHeader()?>
            <div class="content">
                <div class="register">
                    <div></div>
                    <form class="-form register__box" action="server.php" method="post" enctype="multipart/form-data">
                        <div class="register__box__title -center -title">
                            Register Email
                        </div>
                        <div class="register__box__label -center">
                            Your email hasn't been registered yet.<br>
                            Enter your username:
                        </div>
                        <div class="register__box__warning -center -subtitle">
                            IMPORTANT:<br>
                            The account will be permanently tied to your email and cannot be changed.<br>
                            If you lose access to your email, you will not be able to recover it.
                        </div>
                        <div class="register__box__input">
                            <input class="-input -script__alphanum" name="username" placeholder="Username..." maxlength="20" required>
                        </div>
                        <div class="register__box__button -center">
                            <button class="-button" name="method" value="register">
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