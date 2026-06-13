<?php

require_once "common.php";

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
        case "edit_profile":
            editProfile();
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

    $code = "";

    for ($i = 0; $i < 6; $i++) {
        $code .= rand(0, 9);
    }

    session_start();
    $_SESSION["code"] = $code;
    $_SESSION["email"] = $_POST["email"];

    $response = fetch("https://api.sendinblue.com/v3/smtp/email", [
        "method" => "POST",
        "headers" => [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "Api-Key" => $BREVO_API_KEY
        ],
        "body" => json_encode([
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
        ])
    ]);

    if ($response["ok"] == false) {
        alert("Failed to send email.");
    }

    header("Location: login/verify/");
}

function verify() {
    $db = new SQLite3("database.db");
    session_start();

    if ($_POST["code"] != $_SESSION["code"]) {
        alert("Invalid code.");
    }

    $query = <<<SQL
        SELECT * FROM `users` WHERE `email` = :email
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":email", $_SESSION["email"]);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user == false) {
        header("Location: register/");
        exit;
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
    exit;
}

function editProfile() {
    $db = new SQLite3("database.db");
    $user = getUser();

    if ($user == false) {
        alert("You must be logged in to edit your profile.");
    }

    if ($_FILES["avatar"]["error"] != 4) {
        if ($_FILES["avatar"]["size"] > 4000000) {
            alert("Your avatar was too large.\n\nMax size: 4MB.");
        }

        if ($_FILES["avatar"]["error"] != 0) {
            alert("There was an error uploading your avatar.");
        }

        $filename = uniqid("avatar-") . "." . pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);

        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], "uploads/avatars/{$filename}") == false) {
            alert("There was an error uploading your avatar.");
        }

        $query = <<<SQL
            UPDATE `users` SET `avatar` = :avatar WHERE `id` = :id
        SQL;

        $stmt = $db->prepare($query);
        $stmt->bindValue(":avatar", $filename);
        $stmt->bindValue(":id", $user["id"]);
        $stmt->execute();
    }

    if ($_POST["username"] != $user["username"]) {
        if (getUserByName($_POST["username"]) != false) {
            alert("That username is already taken.");
        }
    }

    $query = <<<SQL
        UPDATE `users` SET `username` = :username, `description` = :description WHERE `id` = :id
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":username", $_POST["username"]);
    $stmt->bindValue(":description", $_POST["description"]);
    $stmt->bindValue(":id", $user["id"]);
    $stmt->execute();
    header("Location: user/?id={$_POST["username"]}");
}

function defaultMethod() {
    session_start();

    breakpoint([
        "post" => $_POST,
        "files" => $_FILES,
        "session" => $_SESSION
    ]);
}