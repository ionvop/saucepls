<?php

include("config.php");

/**
 * Enable debugging.
 */
function Debug() {
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
}

/**
 * Prints the given message and exits the script.
 *
 * @param mixed $message The message to be printed.
 * @return void
 */
function Breakpoint($message) {
    header("Content-type: application/json");
    print_r($message);
    exit();
}

function Icon($icon) {
    switch ($icon) {
        case "search":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M380-320q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l224 224q11 11 11 28t-11 28q-11 11-28 11t-28-11L532-372q-30 24-69 38t-83 14Zm0-80q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>
            HTML;
        case "login":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M520-120q-17 0-28.5-11.5T480-160q0-17 11.5-28.5T520-200h240v-560H520q-17 0-28.5-11.5T480-800q0-17 11.5-28.5T520-840h240q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H520Zm-73-320H160q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520h287l-75-75q-11-11-11-27t11-28q11-12 28-12.5t29 11.5l143 143q12 12 12 28t-12 28L429-309q-12 12-28.5 11.5T372-310q-11-12-10.5-28.5T373-366l74-74Z"/></svg>
            HTML;
        case "note_add":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h287q16 0 30.5 6t25.5 17l194 194q11 11 17 25.5t6 30.5v447q0 33-23.5 56.5T720-80H240Zm280-560q0 17 11.5 28.5T560-600h160L520-800v160Zm-80 280v80q0 17 11.5 28.5T480-240q17 0 28.5-11.5T520-280v-80h80q17 0 28.5-11.5T640-400q0-17-11.5-28.5T600-440h-80v-80q0-17-11.5-28.5T480-560q-17 0-28.5 11.5T440-520v80h-80q-17 0-28.5 11.5T320-400q0 17 11.5 28.5T360-360h80Z"/></svg>
            HTML;
        case "logout":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h240q17 0 28.5 11.5T480-800q0 17-11.5 28.5T440-760H200v560h240q17 0 28.5 11.5T480-160q0 17-11.5 28.5T440-120H200Zm487-320H400q-17 0-28.5-11.5T360-480q0-17 11.5-28.5T400-520h287l-75-75q-11-11-11-27t11-28q11-12 28-12.5t29 11.5l143 143q12 12 12 28t-12 28L669-309q-12 12-28.5 11.5T612-310q-11-12-10.5-28.5T613-366l74-74Z"/></svg>
            HTML;
        case "notifications":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M200-200q-17 0-28.5-11.5T160-240q0-17 11.5-28.5T200-280h40v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h40q17 0 28.5 11.5T800-240q0 17-11.5 28.5T760-200H200ZM480-80q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80Z"/></svg>
            HTML;
        case "mail":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-287q5 0 10.5-1.5T501-453l283-177q8-5 12-12.5t4-16.5q0-20-17-30t-35 1L480-520 212-688q-18-11-35-.5T160-659q0 10 4 17.5t12 11.5l283 177q5 3 10.5 4.5T480-447Z"/></svg>
            HTML;
        case "heart_plus":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M760-280q-17 0-28.5-11.5T720-320v-80h-80q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480h80v-80q0-17 11.5-28.5T760-600q17 0 28.5 11.5T800-560v80h80q17 0 28.5 11.5T920-440q0 17-11.5 28.5T880-400h-80v80q0 17-11.5 28.5T760-280ZM70-15v-152 152Zm370-132q-14 0-28-5t-25-16q-44-40-104-91T169-368.5Q115-427 77.5-491T40-621q0-94 63-156.5T260-840q52 0 99 21.5t81 61.5q34-40 81-61.5t99-21.5q63 0 111.5 29.5T808-736q11 19-3.5 38.5T763-680h-16q-85 0-156 68.5T520-440q0 42 13.5 79.5T576-292q11 12 9.5 28T572-237l-79 70q-11 11-25 15.5t-28 4.5Z"/></svg>
            HTML;
        case "flag":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M280-400v240q0 17-11.5 28.5T240-120q-17 0-28.5-11.5T200-160v-600q0-17 11.5-28.5T240-800h287q14 0 25 9t14 23l10 48h184q17 0 28.5 11.5T800-680v320q0 17-11.5 28.5T760-320H553q-14 0-25-9t-14-23l-10-48H280Z"/></svg>
            HTML;
        case "settings":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M433-80q-27 0-46.5-18T363-142l-9-66q-13-5-24.5-12T307-235l-62 26q-25 11-50 2t-39-32l-47-82q-14-23-8-49t27-43l53-40q-1-7-1-13.5v-27q0-6.5 1-13.5l-53-40q-21-17-27-43t8-49l47-82q14-23 39-32t50 2l62 26q11-8 23-15t24-12l9-66q4-26 23.5-44t46.5-18h94q27 0 46.5 18t23.5 44l9 66q13 5 24.5 12t22.5 15l62-26q25-11 50-2t39 32l47 82q14 23 8 49t-27 43l-53 40q1 7 1 13.5v27q0 6.5-2 13.5l53 40q21 17 27 43t-8 49l-48 82q-14 23-39 32t-50-2l-60-26q-11 8-23 15t-24 12l-9 66q-4 26-23.5 44T527-80h-94Zm49-260q58 0 99-41t41-99q0-58-41-99t-99-41q-59 0-99.5 41T342-480q0 58 40.5 99t99.5 41Z"/></svg>
            HTML;
        case "upload":
            return <<<HTML
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M240-160q-33 0-56.5-23.5T160-240v-80q0-17 11.5-28.5T200-360q17 0 28.5 11.5T240-320v80h480v-80q0-17 11.5-28.5T760-360q17 0 28.5 11.5T800-320v80q0 33-23.5 56.5T720-160H240Zm200-486-75 75q-12 12-28.5 11.5T308-572q-11-12-11.5-28t11.5-28l144-144q6-6 13-8.5t15-2.5q8 0 15 2.5t13 8.5l144 144q12 12 11.5 28T652-572q-12 12-28.5 12.5T595-571l-75-75v286q0 17-11.5 28.5T480-320q-17 0-28.5-11.5T440-360v-286Z"/></svg>
            HTML;
    }
}

function SetHeader() {
    $user = GetUserData();
    $searchIcon = Icon("search");
    $loginIcon = Icon("login");
    $noteAddIcon = Icon("note_add");
    $logoutIcon = Icon("logout");
    $notificationsIcon = Icon("notifications");
    $mailIcon = Icon("mail");

    if ($user == false) {
        return <<<HTML
            <div class="-header">
                <div class="-header__title -script__link" data-href="./">
                    SaucePls
                </div>
                <div></div>
                <form class="-form -header__search" action="search/" method="get">
                    <div class="-header__search__input">
                        <input type="text" class="-input">
                    </div>
                    <div class="-header__search__button -center__flex">
                        <button class="-button -button--active">
                            {$searchIcon}
                        </button>
                    </div>
                </form>
                <div></div>
                <div class="-header__login -header__tab -center__flex -script__link" data-href="login/">
                    <div class="-header__login__content -header__tab__content">
                        <div class="-header__login__content__icon -header__tab__content__icon">
                            {$loginIcon}
                        </div>
                        <div class="-header__login__content__text -header__tab__content__text">
                            Login
                        </div>
                    </div>
                </div>
            </div>
        HTML;
    }

    return <<<HTML
        <div class="-header -header--user">
            <div class="-header__title -script__link" data-href="./">
                SaucePls
            </div>
            <div class="-header__new -center__flex">
                <button class="-button -button--active -center__flex -script__link" data-href="new/">
                    <div class="-header__new__icon -center__flex">
                        {$noteAddIcon}
                    </div>
                    <div class="-header__new__text -center__flex">
                        Request
                    </div>
                </button>
            </div>
            <form class="-form -header__search" action="search/" method="get">
                <div class="-header__search__input">
                    <input type="text" class="-input">
                </div>
                <div class="-header__search__button -center__flex">
                    <button class="-button -button--active">
                        {$searchIcon}
                    </button>
                </div>
            </form>
            <div></div>
            <div class="-header__notifications -header__tab -center__flex -script__link" data-href="notifications/">
                <div class="-header__notifications__content -header__tab__content">
                    <div class="-header__notifications__content__icon -header__tab__content__icon">
                        {$notificationsIcon}
                    </div>
                    <div class="-header__notifications__content__text -header__tab__content__text">
                        0
                    </div>
                </div>
            </div>
            <div class="-header__mail -header__tab -center__flex -script__link" data-href="mail/">
                <div class="-header__mail__content -header__tab__content">
                    <div class="-header__mail__content__icon -header__tab__content__icon">
                        {$mailIcon}
                    </div>
                    <div class="-header__mail__content__text -header__tab__content__text">
                        0
                    </div>
                </div>
            </div>
            <div class="-header__user -header__tab -center__flex -script__link" data-href="user/?id={$user['username']}">
                <div class="-header__user__content -header__tab__content">
                    <div class="-header__user__content__avatar -header__tab__content__avatar -center__flex">
                        <img src="uploads/avatars/{$user['avatar']}">
                    </div>
                    <div class="-header__user__content__username -header__tab__content__text">
                        {$user['username']}
                    </div>
                </div>
            </div>
            <form class="-form -header__logout -header__tab -center__flex" action="server.php" method="post" enctype="multipart/form-data" onclick="if (confirm('Are you sure you want to logout?')) this.submit()">
                <div class="-header__logout__content -header__tab__content">
                    <div class="-header__logout__content__icon -header__tab__content__icon -center__flex">
                        {$logoutIcon}
                    </div>
                    <div class="-header__logout__content__text -header__tab__content__text -center__flex">
                        Logout
                    </div>
                </div>
                <input type="hidden" name="method" value="logout">
            </form>
        </div>
    HTML;
}

/**
 * Sends an HTTP request using cURL and returns the response.
 *
 * This function initiates a cURL session to send an HTTP request to the specified URL using the given method, headers, 
 * and data. It supports custom request methods and bypasses SSL verification. If the request fails, the function returns false.
 *
 * @param string $url     The URL to which the request is sent.
 * @param string $method  The HTTP method to use for the request (e.g., 'GET', 'POST', 'PUT', 'DELETE').
 * @param array  $headers An array of HTTP headers to include in the request.
 * @param mixed  $data    The data to send with the request. Typically an associative array or a JSON string.
 *
 * @return mixed The response from the server as a string, or false if the request fails.
 */
function SendCurl($url, $method, $headers, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);

    if (curl_errno($ch) != 0) {
        return false;
    }

    curl_close($ch);
    return $result;
}

/**
 * Prints the given message as an alert and redirects the user.
 *
 * @param mixed $message The message to be displayed.
 * @param string $redirect The URL to redirect the user to. If empty, the user will be redirected back.
 * @return void
 */
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

    exit();
}

function GetSiteData() {
    if (file_exists("data.json") == false) {
        file_put_contents("data.json", file_get_contents("data-template.json"));
    }

    $data = file_get_contents("data.json");
    $data = json_decode($data, true);
    return $data;
}

function SetSiteData($input) {
    $input = json_encode($input, JSON_PRETTY_PRINT);

    if ($input == false) {
        return false;
    }

    if (file_exists("log") == false) {
        mkdir("log");
    }
    
    $date = date("Y-m-d H-i-s");

    if (file_put_contents("log/{$date}.json", $input) == false) {
        return false;
    }

    return file_put_contents("data.json", $input);
}

/**
 * Searches for the first occurrence of an item in an array based on a specific key-value pair and returns its index.
 *
 * @param array  $input The array to search within.
 * @param string $key   The key to look for in each item of the array.
 * @param mixed  $value The value to match against the specified key in each item.
 *
 * @return int The index of the first matching item if found; otherwise, -1.
 */
function FindIndex($input, $key, $value) {
    foreach ($input as $index => $item) {
        if ($item[$key] == $value) {
            return $index;
        }
    }

    return -1;
}

function NewSession($userId) {
    $data = GetSiteData();
    $userIndex = FindIndex($data["users"], "id", $userId);
    
    if ($userIndex == -1) {
        return false;
    }

    $user = $data["users"][$userIndex];

    $newSession = [
        "id" => uniqid("session"),
        "userId" => $user["id"],
        "expiry" => time() + 86400,
        "time" => time()
    ];
    
    $data["sessions"][] = $newSession;
    
    if (SetSiteData($data) == false) {
        return false;
    }

    return $newSession["id"];
}

/**
 * Authenticates a user based on a provided session ID.
 *
 * This function checks if the session ID exists, is not expired, and corresponds to a valid user. 
 * If all checks pass, it returns the user's ID; otherwise, it returns false.
 *
 * @param string $sessionId The session ID to authenticate.
 *
 * @return mixed The user's ID (string) if authentication is successful; otherwise, false.
 */
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

    $userIndex = FindIndex($data["users"], "id", $session["userId"]);

    if ($userIndex == -1) {
        return false;
    }

    $user = $data["users"][$userIndex];
    return $user["id"];
}

/**
 * Retrieves the authenticated user's data based on the session cookie.
 *
 * This function checks for a valid session cookie, authenticates the user, and retrieves their data 
 * from the site's data store. If the user is not authenticated or the session is invalid, it clears 
 * the session cookie and returns false.
 *
 * @return mixed An associative array containing the user's data if authenticated; otherwise, false.
 */
function GetUserData() {
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

    $user = $data["users"][$userIndex];
    $user["lastSeen"] = time();
    $data["users"][$userIndex] = $user;

    if (SetSiteData($data) == false) {
        setcookie("session", "", time()-3600);
        return false;
    }

    return $user;
}

function TimeAgo($timestamp) {
    $now = time(); // Current Unix timestamp
    $diff = $now - $timestamp; // Difference in seconds

    if ($diff < 60) {
        return "less than a minute ago";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($diff < 604800) { // Less than 7 days
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } elseif ($diff < 2592000) { // Less than ~30 days (approx 1 month)
        $weeks = floor($diff / 604800);
        return $weeks . " week" . ($weeks > 1 ? "s" : "") . " ago";
    } elseif ($diff < 31536000) { // Less than 1 year
        $months = floor($diff / 2592000);
        return $months . " month" . ($months > 1 ? "s" : "") . " ago";
    } else {
        $years = floor($diff / 31536000);
        return $years . " year" . ($years > 1 ? "s" : "") . " ago";
    }
}