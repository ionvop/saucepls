<?php

chdir("../../");
include "common.php";

?>

<html>
    <head>
        <title>
            Verify | SaucePls
        </title>
        <base href="../../">
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body > .main {
                display: grid;
                grid-template-rows: max-content 1fr;
                height: 100%;

                & > .content {
                    & > .verify {
                        background-color: #222;
                        border-radius: 1rem;

                        & > .subtitle {
                            padding-top: 0rem;
                        }

                        & > .input {
                            & > input {
                                width: 20rem;
                            }
                        }
                    }
                }
            }
        </style>
    </head>
    <body>
        <div class="main">
            <?=renderHeader()?>
            <div class="content -center__flex">
                <form action="server.php" class="-form verify -pad" method="post" enctype="multipart/form-data">
                    <div class="title -title -pad -center">
                        Enter your login code
                    </div>
                    <div class="subtitle -subtitle -pad -center">
                        Don't forget to check your spam folder.
                    </div>
                    <div class="input -pad -center">
                        <input class="-input" name="code" placeholder="Code">
                    </div>
                    <div class="send -pad -center">
                        <button class="-button" name="method" value="verify">
                            <div class="-iconlabel">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m382-354 339-339q12-12 28-12t28 12q12 12 12 28.5T777-636L410-268q-12 12-28 12t-28-12L182-440q-12-12-11.5-28.5T183-497q12-12 28.5-12t28.5 12l142 143Z"/></svg>
                                </div>
                                <div class="label">
                                    Verify
                                </div>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <script src="script.js"></script>
        <script>

        </script>
    </body>
</html>