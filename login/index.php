<?php

?>

<html>
    <head>
        <title>
            Login | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .main__login {
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

            a[href="register/"] {
                margin-top: 1rem;
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="main__login">
            <a href="./">
                <div class="-title -center">
                    SaucePls
                </div>
            </a>
            <div class="-form-panel -center">
                <form action="server.php" method="post" enctype="multipart/form-data">
                    <div class="-title">
                        Login
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
                        <input type="password" name="password" class="-input">
                    </div>
                    <div class="-form-panel__section">
                        <button name="method" value="login" class="-button">
                            Login
                        </button>
                    </div>
                    <a href="register/">
                        Register
                    </a>
                </form>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>

    </script>
</html>