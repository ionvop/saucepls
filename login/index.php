<html>
    <head>
        <base href="../">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .content {
                padding: 5rem;
            }

            .content__title {
                padding: 1rem;
                font-size: 3rem;
                user-select: none;
                cursor: pointer;
            }

            .-form {
                width: 30%;
                margin-top: 3rem;
            }
        </style>
    </head>
    <body>
        <div class="main__login">
            <div class="content">
                <div class="content__title -center" onclick="btnTitle(this)">
                    SaucePls
                </div>
                <div class="-form -center--block">
                    <div class="-form__title -center">
                        Login
                    </div>
                    <div class="-form__field">
                        <div class="-form__field__label">
                            Username:
                        </div>
                        <div class="-form__field__input">
                            <input class="-input" name="username">
                        </div>
                    </div>
                    <div class="-form__field">
                        <div class="-form__field__label">
                            Password:
                        </div>
                        <div class="-form__field__input">
                            <input type="password" class="-input" name="password">
                        </div>
                    </div>
                    <div class="-form__submit">
                        <div class="-form__submit__button -center">
                            <button class="-button" onclick="btnLogin(this)">
                                Login
                            </button>
                        </div>
                        <div class="-form__submit__other -center" onclick="btnRegister(this)">
                            Register
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form action="server.php" method="post" enctype="multipart/form-data" style="display: none;">
            <input name="username">
            <input type="password" name="password">
            <input name="method" value="login">
        </form>
    </body>
    <script src="script.js"></script>
    <script>
        function btnTitle(element) {
            location.href = "./";
        }
        
        function btnLogin(element) {
            document.querySelector("body > form > input[name='username']").value = document.querySelector(".content .-form input[name='username']").value;
            document.querySelector("body > form > input[name='password']").value = document.querySelector(".content .-form input[name='password']").value;
            document.querySelector("body > form").submit();
        }

        function btnRegister(element) {
            location.href = "register/";
        }
    </script>
</html>