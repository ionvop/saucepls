<?php

chdir("../../");
include("common.php");
Debug();

?>

<html>
    <head>
        <title>
            Verify | SaucePls
        </title>
        <base href="../../">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .content {
                padding: 5rem;
            }

            .verify {
                display: grid;
                grid-template-columns: 1fr max-content 1fr;
            }

            .verify__box {
                padding: 1rem;
                background-color: #222;
                border-radius: 1rem;
            }

            .verify__box__title {
                padding: 1rem;
            }

            .verify__box__label {
                padding: 1rem;
            }

            .verify__box__note {
                padding: 1rem;
            }

            .verify__box__input {
                padding: 1rem;
            }

            .verify__box__button {
                padding: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="main__login__verify">
            <?=SetHeader()?>
            <div class="content">
                <div class="verify">
                    <div></div>
                    <form class="-form verify__box" action="server.php" method="post" enctype="multipart/form-data">
                        <div class="verify__box__title -center -title">
                            Verify Email
                        </div>
                        <div class="verify__box__label -center">
                            Enter the code that was sent to your email:
                        </div>
                        <div class="verify__box__note -subtitle -center">
                            Don't forget to check your spam folder.
                        </div>
                        <div class="verify__box__input">
                            <input class="-input" name="code" placeholder="Code...">
                        </div>
                        <div class="verify__box__button -center">
                            <button class="-button" name="method" value="verify">
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