<?php

chdir("../");
include "common.php";

?>

<html>
    <head>
        <title>
            Register | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body > .main {
                display: grid;
                grid-template-rows: max-content 1fr;
                height: 100%;
                
                & > .content {
                    & > .register {
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
                <form action="server.php" class="-form register -pad" method="post" enctype="multipart/form-data">
                    <div class="title -title -pad -center">
                        Enter your username
                    </div>
                    <div class="subtitle -subtitle -pad -center">
                        Your email hasn't been registered yet.<br><br>
                        IMPORTANT:<br>
                        The account will be permanently tied to your email and cannot be changed.<br>
                        If you lose access to your email, you might not be able to recover it.
                    </div>
                    <div class="input -pad -center">
                        <input class="-input" name="username" placeholder="Username">
                    </div>
                    <div class="send -pad -center">
                        <button class="-button" name="method" value="register">
                            <div class="-iconlabel">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M400-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160q-33 0-56.5-23.5T80-240v-32q0-33 17-62t47-44q51-26 115-44t141-18h26.5q12.5 0 25.5 2 20 2 26 21t-8 33l-17 17q-31 31-35 73t19 77q12 19 3.5 38T412-160H160Zm462-96 174-174q11-11 28-11t28 11q11 11 11 28t-11 28L650-172q-12 12-28 12t-28-12l-82-82q-11-11-11-28t11-28q11-11 28-11t28 11l54 54Z"/></svg>
                                </div>
                                <div class="label">
                                    Register
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