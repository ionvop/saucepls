<?php

include("common.php");
Debug();

if (isset($_POST["method"])) {
    switch ($_POST["method"]) {
        case "login":
            Login();
            break;
        case "register":
            Register();
            break;
        case "logout":
            Logout();
        case "upload":
            Upload();
        default:
            DefaultMethod();
            break;
    }
}

function Login() {
    $data = GetSiteData();

    if ($_POST["username"] == "") {
        Alert("Username is empty.");
    }

    if ($_POST["password"] == "") {
        Alert("Password is empty.");
    }

    $userIndex = FindIndex($data["users"], "username", $_POST["username"]);

    if ($userIndex == -1) {
        Alert("Invalid credentials.");
    }

    $user = $data["users"][$userIndex];

    if (PasswordStretch($user["id"], $_POST["password"]) != $user["hash"]) {
        Alert("Invalid credentials.");
    }

    setcookie("session", NewSession($user["id"]));
    header("Location: ./");
}

function Register() {
    $data = GetSiteData();

    if ($_POST["username"] == "") {
        Alert("Username is empty.");
    }

    if ($_POST["password"] == "") {
        Alert("Password is empty.");
    }

    if ($_POST["email"] == "") {
        Alert("Email is empty.");
    }

    $userIndex = FindIndex($data["users"], "username", $_POST["username"]);

    if ($userIndex != -1) {
        Alert("Username already taken.");
    }

    if ($_POST["password"] < 4) {
        Alert("Your password is too short. Please have at least 4 characters.");
    }

    if ($_POST["password"] != $_POST["repassword"]) {
        Alert("Your passwords do not match.");
    }

    if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) == false) {
        Alert("Your email is invalid.");
    }

    $newUser = [
        "id" => uniqid("user"),
        "username" => $_POST["username"],
        "hash" => "",
        "email" => $_POST["email"],
        "avatar" => "default.jpg",
        "time" => time()
    ];

    $newUser["hash"] = PasswordStretch($newUser["id"], $_POST["password"]);
    $data["users"][] = $newUser;
    
    if (SetSiteData($data) == false) {
        Alert("There was an oopsy woopsy, a little fucky wucky processing the data. Pls check if you typed any invalid characters.");
    }

    setcookie("session", NewSession($newUser["id"]));
    header("Location: ./");
}

function Logout() {
    setcookie("session", "", time()-3600);
    header("Location: ./");
}

function Upload() {
    $data = GetSiteData();
    $user = GetUserData();

    if ($user == false) {
        Alert("Session expired.");
    }

    if ($_FILES["image"]["size"] > 2000000) {
        Alert("Your image file is too big. It won't fit in me, onii-chan <3 ~\n\nMax filesize: 2MB");
    }

    if ($_FILES["image"]["error"] != 0) {
        Alert("Your file is too dirty, onii-chan. You can't put it in yet <3 ~");
    }

    $filename = RenameFile($_FILES["image"]["name"], uniqid("post"));

    if (move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/posts/" . $filename) == false) {
        Alert("There was an oopsy woopsy, a little fucky wucky uploading your image. Please try again.");
    }

    $newPost = [
        "id" => uniqid("post"),
        "uploader" => $user["id"],
        "file" => $filename,
        "text" => $_POST["text"],
        "tags" => $_POST["tags"],
        "description" => $_POST["description"],
        "time" => time()
    ];

    $data["posts"][] = $newPost;

    if (SetSiteData($data) == false) {
        Alert("There was an oopsy woopsy, a little fucky wucky processing the data. Pls check if you typed any invalid characters.");
    }

    header("Location: post/?id=" . $newPost["id"]);
}

function DefaultMethod() {
    echo "Hello, world!";
}

?>