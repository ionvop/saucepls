<?php

chdir("../");
include "common.php";

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
            body > .main {
                & > .content {
                    & > .login {
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
        <div class="main -main">
            <?=renderHeader()?>
            <div class="content -center__flex">
                <form action="server.php" class="-form login -pad" method="post" enctype="multipart/form-data">
                    <div class="title -title -pad -center">
                        Enter your email
                    </div>
                    <div class="subtitle -subtitle -pad -center">
                        A code will be sent to your email which will be<br>
                        used for logging in or for registering an account.
                    </div>
                    <div class="input -pad -center">
                        <input type="email" class="-input" name="email" placeholder="Email">
                    </div>
                    <div class="send -pad -center">
                        <button class="-button" name="method" value="login">
                            <div class="-iconlabel">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M520-120q-17 0-28.5-11.5T480-160q0-17 11.5-28.5T520-200h240v-560H520q-17 0-28.5-11.5T480-800q0-17 11.5-28.5T520-840h240q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H520Zm-73-320H160q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520h287l-75-75q-11-11-11-27t11-28q11-12 28-12.5t29 11.5l143 143q12 12 12 28t-12 28L429-309q-12 12-28.5 11.5T372-310q-11-12-10.5-28.5T373-366l74-74Z"/></svg>
                                </div>
                                <div class="label">
                                    Login
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