<?php

include("common.php");
Debug();

if (isset($_POST["method"])) {
    switch ($_POST["method"]) {
        case "login":
            Login();
            break;
        case "verify":
            Verify();
            break;
        case "register":
            Register();
            break;
        case "logout":
            Logout();
            break;
        case "editProfile":
            EditProfile();
            break;
        case "newRequest":
            NewRequest();
            break;
        default:
            DefaultMethod();
            break;
    }
} else {
    DefaultMethod();
}

function Login() {
    global $BREVO_API_KEY;

    if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) == false) {
        Alert("Invalid email.");
    }

    $code = substr(md5(time()), 0, 5);
    session_start();

    $_SESSION["code"] = $code;
    $_SESSION["email"] = $_POST["email"];

    $headers = [
        "Content-Type: application/json",
        "Accept: application/json",
        "Api-Key: {$BREVO_API_KEY}"
    ];

    $body = [
        "sender" => [
            "name" => "SaucePls",
            "email" => "ionvop@gmail.com"
        ],
        "to" => [
            [
                "email" => $_POST["email"]
            ]
        ],
        "textContent" => "Your login code is: {$code}\n\nIf you did not request this code, please ignore this email.",
        "subject" => "SaucePls login code"
    ];

    echo <<<HTML
        <h1>{$body["subject"]}</h1>
        <pre>{$body["textContent"]}</pre><br><br>
        <a href="login/verify/">
            <button>
                Continue
            </button>
        </a>
    HTML;

    exit();

    SendCurl("https://api.sendinblue.com/v3/smtp/email", "POST", $headers, json_encode($body));
    header("Location: login/verify/");
    exit();
}

function Verify() {
    $db = new SQLite3("database.db");
    session_start();

    if ($_POST["code"] != $_SESSION["code"]) {
        Alert("Incorrect code.");
    }

    $query = <<<SQL
        SELECT * FROM `users` WHERE `email` = :email
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":email", $_SESSION["email"]);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user == false) {
        header("Location: register/");
        exit();
    }

    session_destroy();
    $session = uniqid("session-");

    $query = <<<SQL
        UPDATE `users` SET `session` = :session WHERE `id` = :id
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":session", $session);
    $stmt->bindValue(":id", $user["id"]);
    $stmt->execute();
    setcookie("session", $session, time() + (86400 * 30));
    header("Location: ./");
}

function Register() {
    $db = new SQLite3("database.db");

    if (strlen($_POST["username"]) < 4) {
        Alert("Your username must be at least 4 characters long.");
    }

    if (strlen($_POST["username"]) > 20) {
        Alert("Your username must be less than 20 characters long.");
    }

    if (preg_match("/[^a-zA-Z0-9-_]/", $_POST["username"])) {
        Alert("Your username can only contain letters, numbers, underscores, and hyphens.");
    }

    session_start();

    $query = <<<SQL
        SELECT * FROM `users` WHERE `username` = :username OR `email` = :email
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":username", $_POST["username"]);
    $stmt->bindValue(":email", $_SESSION["email"]);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user != false) {
        Alert("That username is already taken.");
    }

    $session = uniqid("session-");

    $query = <<<SQL
        INSERT INTO `users` (`username`, `email`, `session`) VALUES (:username, :email, :session)
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":username", $_POST["username"]);
    $stmt->bindValue(":email", $_SESSION["email"]);
    $stmt->bindValue(":session", $session);
    $stmt->execute();
    session_destroy();
    setcookie("session", $session, time() + (86400 * 30));
    header("Location: ./");
    exit();
}

function Logout() {
    $db = new SQLite3("database.db");
    $user = GetUser();

    $query = <<<SQL
        UPDATE `users` SET `session` = NULL WHERE `id` = :id
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":id", $user["id"]);
    $stmt->execute();
    setcookie("session", "", time() - 3600);
    header("Location: ./");
    exit();
}

function EditProfile() {
    $db = new SQLite3("database.db");
    $user = GetUser();

    if ($user == false) {
        Alert("Unauthorized.");
    }

    if (strlen($_POST["username"]) < 4) {
        Alert("Your username must be at least 4 characters long.");
    }

    if (strlen($_POST["username"]) > 20) {
        Alert("Your username must be less than 20 characters long.");
    }

    if (preg_match("/[^a-zA-Z0-9-_]/", $_POST["username"])) {
        Alert("Your username can only contain letters, numbers, underscores, and hyphens.");
    }

    if (strlen($_POST["description"]) > 10000) {
        Alert("Your description must be less than 10000 characters long.");
    }

    if ($_POST["username"] != $user["username"]) {
        $query = <<<SQL
            SELECT * FROM `users` WHERE `username` = :username
        SQL;

        $stmt = $db->prepare($query);
        $stmt->bindValue(":username", $_POST["username"]);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result != false) {
            Alert("That username is already taken.");
        }

        $query = <<<SQL
            UPDATE `users` SET `username` = :username WHERE `id` = :id    
        SQL;

        $stmt = $db->prepare($query);
        $stmt->bindValue(":username", $_POST["username"]);
        $stmt->bindValue(":id", $user["id"]);
        $stmt->execute();
    }

    if ($_FILES["avatar"]["error"] == 0) {
        if ($_FILES["avatar"]["size"] > 2000000) {
            Alert("Your avatar must be less than 2MB in size.");
        }

        $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $filename = uniqid("avatar-") . "." . $extension;
        move_uploaded_file($_FILES["avatar"]["tmp_name"], "uploads/avatars/{$filename}");

        $query = <<<SQL
            UPDATE `users` SET `avatar` = :avatar WHERE `id` = :id
        SQL;

        $stmt = $db->prepare($query);
        $stmt->bindValue(":avatar", $filename);
        $stmt->bindValue(":id", $user["id"]);
        $stmt->execute();
    }

    $query = <<<SQL
        UPDATE `users` SET `description` = :description WHERE `id` = :id
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":description", $_POST["description"]);
    $stmt->bindValue(":id", $user["id"]);
    $stmt->execute();
    header("Location: user/?id={$user['username']}");
    exit();
}

function NewRequest() {
    $db = new SQLite3("database.db");
    $user = GetUser();

    if (strlen($_POST["text"]) > 2000) {
        Alert("Your text must be less than 2000 characters long.");
    }

    if (strlen($_POST["description"]) > 2000) {
        Alert("Your description must be less than 2000 characters long.");
    }

    if ($_FILES["image"]["error"] != 0) {
        Alert("There was an error uploading your image.");
    }

    if ($_FILES["image"]["size"] > 2000000) {
        Alert("Your image must be less than 2MB in size.");
    }

    $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
    $filename = uniqid("request") . "." . $extension;
    move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/requests/{$filename}");

    $query = <<<SQL
        INSERT INTO `requests` (`user_id`, `text`, `description`, `image`)
        VALUES (:user_id, :text, :description, :image)
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":user_id", $user["id"]);
    $stmt->bindValue(":text", $_POST["text"]);
    $stmt->bindValue(":description", $_POST["description"]);
    $stmt->bindValue(":image", $filename);
    $stmt->execute();

    $query = <<<SQL
        SELECT * FROM `requests` WHERE `id` = :id
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":id", $db->lastInsertRowID());
    $request = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    foreach ($_POST["tags"] as $tag) {
        if (substr($tag, 0, 1) == "-") {
            continue;
        }

        if (preg_match("/[^a-zA-Z0-9-_]/", $tag)) {
            continue;
        }

        $query = <<<SQL
            SELECT * FROM `tags` WHERE `name` = :name
        SQL;

        $stmt = $db->prepare($query);
        $stmt->bindValue(":name", $tag);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result) {
            $id = $result["id"];
        } else {
            $query = <<<SQL
                INSERT INTO `tags` (`name`)
                VALUES (:name)
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":name", $tag);
            $stmt->execute();
            $id = $db->lastInsertRowID();
        }

        $query = <<<SQL
            INSERT INTO `request_tags` (`request_id`, `tag_id`)
            VALUES (:request_id, :tag_id)
        SQL;

        $stmt = $db->prepare($query);
        $stmt->bindValue(":request_id", $request["id"]);
        $stmt->bindValue(":tag_id", $id);
        $stmt->execute();
    }

    header("Location: request/?id={$request['id']}");
    exit();
}

function DefaultMethod() {
    session_start();

    Breakpoint([
        "post" => $_POST,
        "files" => $_FILES,
        "session" => $_SESSION
    ]);
}