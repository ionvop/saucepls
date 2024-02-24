<?php

function Debug() {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

function Breakpoint($message) {
    header("Content-type: application/json");
    print_r($message);
    exit(200);
}

function Alert($message, $redirect = "") {
    $message = json_encode($message);

    $redirectScript = <<<JS
        window.history.back();
    JS;
    
    if ($redirect != "") {
        $redirect = json_encode($redirect);

        $redirectScript = <<<JS
            location.href = {$redirect};
        JS;
    }

    echo <<<HTML
        <script>
            alert({$message});
            {$redirectScript}
        </script>
    HTML;

    exit(400);
}

function SetHeader() {
    $data = GetSiteData();

    $default = <<<HTML
        <div class="-header">
            <div class="-header__title" onclick="btnHeaderTitle(this)">
                SaucePls
            </div>
            <div class="-header__search">
                <div class="-header__search__input">
                    <input class="-input" placeholder="Search for posts (ex. 1girl touhou emotionless_sex -futanari)">
                </div>
                <div class="-header__search__button">
                    <button class="-button" onclick="btnHeaderSearch(this)">
                        <span class="material-symbols-rounded">
                            search
                        </span>
                    </button>
                    <form action="search/" style="display: none;">
                        <input name="q">
                    </form>
                </div>
            </div>
            <div class="-header__login -header__tab -center--flex" onclick="btnHeaderLogin(this)">
                Login
            </div>
            <div class="-header__register -header__tab -center--flex" onclick="btnHeaderRegister(this)">
                Register
            </div>
        </div>
    HTML;

    $user = GetUserData();

    if ($user == false) {
        return $default;
    }

    $username = htmlentities($user["username"]);
    $avatar = htmlentities($user["avatar"]);

    return <<<HTML
        <div class="-header--user">
            <div class="-header__title" onclick="btnHeaderTitle(this)">
                SaucePls
            </div>
            <div class="-header__search">
                <div class="-header__search__input">
                    <input class="-input" placeholder="Search for posts (ex. 1girl touhou emotionless_sex -futanari)">
                </div>
                <div class="-header__search__button">
                    <button class="-button" onclick="btnHeaderSearch(this)">
                        <span class="material-symbols-rounded">
                            search
                        </span>
                    </button>
                    <form action="search/" style="display: none;">
                        <input name="q">
                    </form>
                </div>
            </div>
            <div class="-header__upload -header__tab -center--flex" onclick="btnHeaderUpload(this)">
                Upload
            </div>
            <div class="-header__profile -header__tab" onclick="btnHeaderProfile(this)" value="{$username}">
                <div class="-header__profile__avatar -center--flex">
                    <img src="uploads/avatar/{$avatar}">
                </div>
                <div class="-header__profile__username -center--flex">
                    {$username}
                </div>
            </div>
            <div class="-header__logout -header__tab -center--flex" onclick="btnHeaderLogout(this)">
                Logout
            </div>
        </div>
    HTML;
}

function GetSiteData() {
    $data = file_get_contents("data.json");
    $data = json_decode($data, true);
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
    if (file_exists("log") == false) {
        mkdir("log");
    }
    
    $date = date("Y-m-d H-i-s");
    return file_put_contents("log/{$date}.json", $input);
}

function FindIndex($input, $key, $value) {
    foreach ($input as $index => $item) {
        if ($item[$key] == $value) {
            return $index;
        }
    }

    return -1;
}

function PasswordStretch($userId, $password) {
    $userId = base64_encode($userId);
    $password = base64_encode($password);
    $result = "";

    while (strlen($password) < strlen($userId)) {
        $password .= $password;
    }

    foreach (str_split($password) as $key => $value) {
        $result .= $value . substr($userId, $key % strlen($userId), 1);
    }

    return $result;
}

function NewSession($userId) {
    $data = GetSiteData();
    $userIndex = FindIndex($data["users"], "id", $userId);
    
    if ($userIndex == -1) {
        return false;
    }

    $user = $data["users"][$userIndex];

    foreach($data["sessions"] as $key => $value) {
        if ($value["userid"] == $user["id"]) {
            unset($data["sessions"][$key]);
        }
    }

    $data["sessions"] = array_values($data["sessions"]);

    $newSession = [
        "id" => uniqid("session"),
        "userid" => $user["id"],
        "expiry" => time() + 86400,
        "time" => time()
    ];
    
    $data["sessions"][] = $newSession;
    
    if (SetSiteData($data) == false) {
        return false;
    }

    return $newSession["id"];
}

function AuthenticateUser($sessionId) {
    $data = GetSiteData();

    $sessionIndex = FindIndex($data["sessions"], "id", $sessionId);

    if ($sessionIndex == -1) {
        return false;
    }

    $session = $data["sessions"][$sessionIndex];
    
    if (time() > $session["expiry"]) {
        return false;
    }

    $userIndex = FindIndex($data["users"], "id", $session["userid"]);

    if ($userIndex == -1) {
        return false;
    }

    $user = $data["users"][$userIndex];

    return $user["id"];
}

function GetUserData($index = false) {
    $data = GetSiteData();

    if (isset($_COOKIE["session"]) == false) {
        setcookie("session", "", time()-3600);
        return false;
    }

    $userId = AuthenticateUser($_COOKIE["session"]);

    if ($userId == false) {
        setcookie("session", "", time()-3600);
        return false;
    }

    $userIndex = FindIndex($data["users"], "id", $userId);

    if ($userIndex == -1) {
        setcookie("session", "", time()-3600);
        return false;
    }

    if ($index == true) {
        return $userIndex;
    }

    return $data["users"][$userIndex];
}

function GetOtherUserData($username) {
    $data = GetSiteData();
    $userIndex = FindIndex($data["users"], "username", $username);

    if ($userIndex == -1) {
        return false;
    }

    return $data["users"][$userIndex];
}

function SortArray($array, $key, $descending = false) {
    usort($array, function ($a, $b) use ($key, $descending) {
        $valueA = $a[$key];
        $valueB = $b[$key];

        if ($valueA == $valueB) {
            return 0;
        }

        return ($valueA < $valueB) ? ($descending ? 1 : -1) : ($descending ? -1 : 1);
    });

    return $array;
}

function RenameFile($input, $new) {
    $inputExt = explode(".", $input);
    $inputExt = end($inputExt);
    $inputExt = strtolower($inputExt);
    $inputExt = ".".$inputExt;
    return $new.$inputExt;
}

function SendCurl($url, $method, $headers, $data) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }

    curl_close($ch);
    return $result;
}

?>