<?php

include("common.php");
Debug();

if (isset($_POST["method"])) {
    switch ($_POST["method"]) {
        case "login":
            Login();
        case "register":
            Register();
        case "updateProfile":
            UpdateProfile();
    }
} else {
    DefaultMethod();
}

function Login() {
    $data = GetSiteData();

    if ($_POST["username"] == "") {
        Alert("Username is empty.");
    }

    if ($_POST["password"] == "") {
        Alert("Password is empty.");
    }

    $userIndex = FindIndexByKeyValue($data->users, "username", $_POST["username"]);

    if ($userIndex == -1) {
        Alert("Username or password is incorrect.");
    }

    $user = $data->users[$userIndex];
    $seed = substr($user->id, 1).substr($user->id, 2).substr($user->id, 3).substr($user->id, 4).substr($user->id, 5).substr($user->id, 6).substr($user->id, 7).substr($user->id, 8).substr($user->id, 9).substr($user->id, 10);
    $seed = base64_encode($seed);

    if (Ivcrypt(0, $user->hash, $_POST["password"]) == $seed) {
        setcookie("sessionid", NewSession($user->id), 0, "/");
        header("Location: ./");
    } else {
        Alert("Username or password is incorrect.");
    }
}

function Register() {
    $data = GetSiteData();

    if ($_POST["username"] == "") {
        Alert("Username is empty.");
    }

    if ($_POST["password"] == "") {
        Alert("Password is empty.");
    }

    if ($_POST["email"] == "" || filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) == false) {
        Alert("The email you have entered is not valid.");
    }

    if ($_POST["password"] != $_POST["repassword"]) {
        Alert("The passwords do not match.");
    }

    if (strlen($_POST["password"]) < 4) {
        Alert("Your password is too short. Please have atleast 4 characters.");
    }

    if (strlen($_POST["password"]) > 64) {
        Alert("Your password is too long. Please have atmost 64 characters.");
    }

    if (FindIndexByKeyValue($data->users, "username", $_POST["username"]) != -1) {
        Alert("Username is already taken.");
    }

    $id = uniqid("user");
    $seed = substr($id, 1).substr($id, 2).substr($id, 3).substr($id, 4).substr($id, 5).substr($id, 6).substr($id, 7).substr($id, 8).substr($id, 9).substr($id, 10);
    $seed = base64_encode($seed);
    $hash = Ivcrypt(1, $seed, $_POST["password"]);
    $avatar = "default.jpg";

    $newUser = new stdClass();
        $newUser->id = $id;
        $newUser->username = $_POST["username"];
        $newUser->email = $_POST["email"];
        $newUser->hash = $hash;
        $newUser->avatar = $avatar;
        $newUser->type = "member";
        $newUser->description = "Hello, world!";
        $newUser->time = time();
    
    array_push($data->users, $newUser);

    if (SetSiteData($data) == false) {
        Alert("There was an oopsy woopsy, a little fucky wucky processing the data. Pls check if you typed any invalid characters.");
    }

    setcookie("sessionid", NewSession($id), 0, "/");
    header("Location: user/?id={$newUser->username}");
}

function UpdateProfile() {
    $data = GetSiteData();
    $userIndex = FindIndexByKeyValue($data->users, "id", AuthenticateUser());

    if ($userIndex == -1) {
        Alert("How tf did you get here. You're not supposed to be here. Get tf out");
    }

    $user = $data->users[$userIndex];

    if ($_FILES["avatar"]["error"] != 4) {
        if ($_FILES["avatar"]["size"] > 2000000) {
            Alert("Your image file is too big. It won't fit in me, onii-chan <3 ~\\n\\nMax filesize: 2MB");
        }
    
        if ($_FILES["bg"]["error"] != 0) {
            Alert("Your file is too dirty, onii-chan. You can't put it in yet <3 ~");
        }
    
        $avatarName = $_FILES["avatar"]["name"];
        $avatarName = RenameFile($avatarName, uniqid("avatar"));
        $avatarPath = $_FILES["avatar"]["tmp_name"];
    
        if (move_uploaded_file($avatarPath, "uploads/avatar/{$avatarName}") == false) {
            Alert("There was an oopsy woopsy, a little fucky wucky uploading your avatar. Please try again.");
        }
    
        $user->avatar = $avatarName;
    }
    
    if ($_POST["username"] != $user->username) {
        if (FindIndexByKeyValue($data->users, "username", $_POST["username"]) != -1) {
            Alert("Username is already taken");
        }

        $user->username = $_POST["username"];
    }

    $user->description = $_POST["description"];
    
    if ($_POST["email"] == "" || filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) == false) {
        Alert("The email you have entered is not valid.");
    }

    $user->email = $_POST["email"];

    if ($_POST["password"] != "") {
        $seed = substr($user->id, 1).substr($user->id, 2).substr($user->id, 3).substr($user->id, 4).substr($user->id, 5).substr($user->id, 6).substr($user->id, 7).substr($user->id, 8).substr($user->id, 9).substr($user->id, 10);
        $seed = base64_encode($seed);

        if (Ivcrypt(0, $user->hash, $_POST["password"]) == $seed) {
            if ($_POST["newPassword"] != $_POST["repassword"]) {
                Alert("The passwords do not match.");
            }
        
            if (strlen($_POST["newPassword"]) < 4) {
                Alert("Your password is too short. Please have atleast 4 characters.");
            }
        
            if (strlen($_POST["newPassword"]) > 64) {
                Alert("Your password is too long. Please have atmost 64 characters.");
            }

            $user->hash = Ivcrypt(1, $seed, $_POST["newPassword"]);
        } else {
            Alert("Password is incorrect");
        }
    }

    if (SetSiteData($data) == false) {
        Alert("There was an oopsy woopsy, a little fucky wucky processing the data. Pls check if you typed any invalid characters.");
    }

    header("Location: user/?id={$user->username}");
}

function DefaultMethod() {
    Breakpoint("Hello, world!");
}

?>