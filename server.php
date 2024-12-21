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
        default:
            DefaultMethod();
            break;
    }
} else {
    DefaultMethod();
}

function Login() {
    global $BREVO_API_KEY;
    $code = substr(md5(time()), 0, 5);
    session_start();

    $_SESSION["verify"] = [
        "email" => $_POST["email"],
        "code" => $code,
        "expiry" => time() + 300
    ];

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

    SendCurl("https://api.sendinblue.com/v3/smtp/email", "POST", $headers, json_encode($body));
    header("Location: login/verify/");
    exit();
}

function Verify() {
    $data = GetSiteData();
    session_start();

    if (time() > $_SESSION["verify"]["expiry"]) {
        Alert("Your code is incorrect or may have expired.");
    }

    if ($_POST["code"] != $_SESSION["verify"]["code"]) {
        Alert("Your code is incorrect or may have expired.");
    }

    $userIndex = FindIndex($data["users"], "email", $_SESSION["verify"]["email"]);

    if ($userIndex == -1) {
        header("Location: register/");
        exit();
    }

    $user = $data["users"][$userIndex];
    $sessionId = NewSession($user["id"]);
    
    if ($sessionId == false) {
        Alert("There was an error logging you in.");
    }

    $_SESSION["verify"] = null;
    setcookie("session", $sessionId, time() + 86400);
    header("Location: ./");
    exit();
}

function Register() {
    $data = GetSiteData();
    session_start();

    if (filter_var($_SESSION["verify"]["email"], FILTER_VALIDATE_EMAIL) == false) {
        Alert("That email is invalid.");
    }

    if (FindIndex($data["users"], "email", $_SESSION["verify"]["email"]) != -1) {
        Alert("That email is already registered.");
    }

    if (strlen($_POST["username"]) < 4) {
        Alert("Your username must be at least 4 characters long.");
    }

    if (strlen($_POST["username"]) > 20) {
        Alert("Your username must be less than 20 characters long.");
    }

    if (preg_match("/^[a-zA-Z0-9_-]+$/", $_POST["username"]) == 0) {
        Alert("Your username can only contain letters, numbers, underscores, and hyphens.");
    }

    if (FindIndex($data["users"], "username", $_POST["username"]) != -1) {
        Alert("That username is already taken.");
    }

    $user = [
        "id" => uniqid("user"),
        "username" => $_POST["username"],
        "email" => $_SESSION["verify"]["email"],
        "avatar" => "default.jpg",
        "description" => "Hello, world!",
        "type" => "member",
        "lastSeen" => time(),
        "time" => time()
    ];

    $data["users"][] = $user;

    if (SetSiteData($data) == false) {
        Alert("There was an error registering your account.");
    }

    $sessionId = NewSession($user["id"]);

    if ($sessionId == false) {
        Alert("There was an error logging you in.");
    }

    $_SESSION["verify"] = null;
    setcookie("session", $sessionId, time() + 86400);
    header("Location: ./");
    exit();
}

function Logout() {
    $data = GetSiteData();

    $data["sessions"] = array_values(array_filter($data["sessions"], function ($session) use ($data) {
        if ($session["id"] == $_COOKIE["session"]) {
            return false;
        }

        return true;
    }));

    if (SetSiteData($data) == false) {
        Alert("There was an error logging you out.");
    }

    setcookie("session", "", time() - 3600);
    header("Location: ./");
    exit();
}

function EditProfile() {
    $data = GetSiteData();
    $user = GetUserData();

    if ($_POST["username"] != $user["username"]) {
        if (strlen($_POST["username"]) < 4) {
            Alert("Your username must be at least 4 characters long.");
        }
    
        if (strlen($_POST["username"]) > 20) {
            Alert("Your username must be less than 20 characters long.");
        }
    
        if (preg_match("/^[a-zA-Z0-9_-]+$/", $_POST["username"]) == 0) {
            Alert("Your username can only contain letters, numbers, underscores, and hyphens.");
        }

        if (FindIndex($data["users"], "username", $_POST["username"]) != -1) {
            Alert("That username is already taken.");
        }

        $user["username"] = $_POST["username"];
    }

    if ($_FILES["avatar"]["error"] == 0) {
        if ($_FILES["avatar"]["size"] > 2000000) {
            Alert("Your avatar must be less than 2MB in size.");
        }

        $extension = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
        $filename = uniqid("avatar") . "." . $extension;

        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], "uploads/avatars/{$filename}") == false) {
            Alert("There was an error uploading your avatar.");
        }

        $user["avatar"] = $filename;
    }

    if (strlen($_POST["description"]) > 10000) {
        Alert("Your description must be less than 10000 characters long.");
    }

    $user["description"] = $_POST["description"];
    $userIndex = FindIndex($data["users"], "id", $user["id"]);

    if ($userIndex == -1) {
        Alert("There was an error updating your profile.");
    }

    $data["users"][$userIndex] = $user;

    if (SetSiteData($data) == false) {
        Alert("There was an error updating your profile.");
    }

    header("Location: user/?id={$user['username']}");
    exit();
}

function DefaultMethod() {
    Breakpoint([
        "post" => $_POST,
        "files" => $_FILES
    ]);
}