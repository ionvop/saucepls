<?php

include("ivcrypt.php");

function Debug() {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

function Breakpoint($message) {
    header("Content-type: application/json");
    print_r($message);
    exit();
}

function Alert($message) {
    echo <<<HTML
        <script>alert("{$message}"); window.history.back();</script>
    HTML;

    exit();
}

function SetHeader() {
    $data = GetSiteData();
    $userId = AuthenticateUser();
    $default = <<<HTML
        <div class="-header">
            <a href="./">
                <div class="-header__title">
                    SaucePls
                </div>
            </a>
            <form action="search/">
                <div class="-header__search">
                    <div class="-header__search__input">
                        <input name="q" placeholder="Search for posts (ex. 1girl touhou emotionless_sex -futanari)" class="-input">
                    </div>
                    <div class="-header__search__button">
                        <button class="-button">
                            <span class="material-symbols-rounded -title2">
                                search
                            </span>
                        </button>
                    </div>
                </div>
            </form>
            <a href="login/">
                <div class="-header__login -header__tab">
                    Login
                </div>
            </a>
            <a href="register/">
                <div class="-header__register -header__tab">
                    Register
                </div>
            </a>
        </div>
    HTML;

    if ($userId == false) {
        return $default;
    }

    $userIndex = FindIndexByKeyValue($data->users, "id", $userId);

    if ($userId == -1) {
        return $default;
    }

    $user = $data->users[$userIndex];

    return <<<HTML
        <div class="-header">
            <a href="./">
                <div class="-header__title">
                    SaucePls
                </div>
            </a>
            <form action="search/">
                <div class="-header__search">
                    <div class="-header__search__input">
                        <input name="q" placeholder="Search for posts (ex. 1girl touhou emotionless_sex -futanari)" class="-input">
                    </div>
                    <div class="-header__search__button">
                        <button class="-button">
                            <span class="material-symbols-rounded -title2">
                                search
                            </span>
                        </button>
                    </div>
                </div>
            </form>
            <a href="user/?id={$user->username}">
                    <div class="-header__profile">
                        <div class="-header__profile__container">
                            <div class="-header__profile__avatar">
                                <img src="uploads/avatar/{$user->avatar}">
                            </div>
                            <div class="-header__profile__username -center--flex">
                                {$user->username}
                            </div>
                        </div>
                    </div>
                </a>
            <a href="upload/">
                <div class="-header__upload -header__tab">
                    Upload
                </div>
            </a>
            <div class="-header__logout -header__tab" onclick="btnLogout()">
                Logout
            </div>
        </div>
    HTML;
}

function GetSiteData() {
    $data = file_get_contents("data.json");
    $data = json_decode($data);
    return $data;
}

function SetSiteData($input) {
    $input = json_encode($input);

    if ($input == false) {
        return false;
    }

    LogData($input);
    return file_put_contents("data.json", $input);
}

function LogData($input) {
    $date = date("Y-m-d H-i-s");
    return file_put_contents("log/{$date}.json", $input);
}

function FindIndexByKeyValue($input, $key, $value) {
    for ($i = 0; $i < count($input); $i++) {
        if ($input[$i]->{$key} == $value) {
            return $i;
        }
    }

    return -1;
}

function NewSession($userId) {
    $data = GetSiteData();
    $userIndex = FindIndexByKeyValue($data->users, "id", $userId);
    
    if ($userIndex == -1) {
        return false;
    }

    $user = $data->users[$userIndex];

    foreach($data->sessions as $key => $element) {
        if ($element->userid == $user->id) {
            unset($data->sessions[$key]);
        }
    }

    $data->sessions = array_values($data->sessions);

    $newSession = new stdClass();
        $newSession->id = uniqid("session");
        $newSession->userid = $user->id;
        $newSession->expiry = time() + 86400;
        $newSession->time = time();
    
    array_push($data->sessions, $newSession);
    
    if (SetSiteData($data) == false) {
        Alert("There was an oopsy woopsy, a little fucky wucky processing the data. Pls check if you typed any invalid characters.");
    }

    return $newSession->id;
}

function AuthenticateUser() {
    $data = GetSiteData();

    if (isset($_COOKIE["sessionid"]) == false) {
        return false;
    }

    $sessionIndex = FindIndexByKeyValue($data->sessions, "id", $_COOKIE["sessionid"]);

    if ($sessionIndex == -1) {
        return false;
    }

    $session = $data->sessions[$sessionIndex];
    $userIndex = FindIndexByKeyValue($data->users, "id", $session->userid);

    if ($userIndex == -1) {
        return false;
    }

    $user = $data->users[$userIndex];
    return $user->id;
}

function RenameFile($input, $new) {
    $inputExt = explode(".", $input);
    $inputExt = end($inputExt);
    $inputExt = strtolower($inputExt);
    $inputExt = ".".$inputExt;
    return $new.$inputExt;
}

?>