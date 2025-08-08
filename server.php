<?php

include "common.php";

if (isset($_POST["method"])) {
    switch ($_POST["method"]) {
        case "login":
            login();
            break;
        case "verify":
            verify();
            break;
        case "register":
            register();
            break;
        case "logout":
            logout();
            break;
        case "upload":
            upload();
            break;
        default:
            defaultMethod();
            break;
    }
} else {
    defaultMethod();
}

function login() {
    global $BREVO_API_KEY;

    if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) == false) {
        alert("Invalid email.");
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
        "textContent" => "Your login code is: {$code}\n\nIf you did not request this code, you can safely ignore this email.",
        "subject" => "SaucePls login code"
    ];

    // for testing
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

    sendCurl("https://api.sendinblue.com/v3/smtp/email", "POST", $headers, json_encode($body));
    header("Location: login/verify/");
    exit();
}

function verify() {
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

function register() {
    $db = new SQLite3("database.db");

    if (strlen($_POST["username"]) < 4) {
        alert("Your username must be at least 4 characters long.");
    }

    if (strlen($_POST["username"]) > 20) {
        alert("Your username must be less than 20 characters long.");
    }

    if (preg_match("/[^a-zA-Z0-9-_]/", $_POST["username"])) {
        alert("Your username can only contain letters, numbers, underscores, and hyphens.");
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
        alert("That username is already taken.");
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
}

function logout() {
    $db = new SQLite3("database.db");
    $user = getUser();

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

function upload() {
    $db = new SQLite3("database.db");
    $user = getUser();

    if ($user == false) {
        alert("You must be logged in to upload files.");
    }

    if ($_FILES["image"]["error"] != 0) {
        alert("There was an error uploading your image.");
    }

    if ($_FILES["image"]["size"] > 4000000) {
        alert("Your image was too large.\n\nMax size: 4MB.");
    }

    $filename = uniqid("post-") . "." . pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/posts/{$filename}") == false) {
        alert("There was an error uploading your image.");
    }

    $query = <<<SQL
        INSERT INTO `posts` (`user_id`, `image`, `title`, `description`, `tags`, `text`)
        VALUES (:user_id, :image, :title, :description, :tags, :text)
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":user_id", $user["id"]);
    $stmt->bindValue(":image", $filename);
    $stmt->bindValue(":title", $_POST["title"]);
    $stmt->bindValue(":description", $_POST["description"]);
    $stmt->bindValue(":tags", $_POST["tags"]);
    $stmt->bindValue(":text", $_POST["text"]);
    $stmt->execute();
    header("Location: post/?id={$db->lastInsertRowID()}");
}

function defaultMethod() {
    session_start();

    Breakpoint([
        "post" => $_POST,
        "files" => $_FILES,
        "session" => $_SESSION
    ]);
}