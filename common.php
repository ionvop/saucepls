<?php

include "config.php";

/**
 * Enable debugging.
 */
function debug() {
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
function breakpoint($message) {
    header("Content-type: application/json");
    print_r($message);
    exit();
}

function renderHeader() {
    return <<<HTML
        <div class="-header">
            <a href="./" class="-a title -title -pad -center__flex">
                SaucePls
            </a>
            <form action="search/" class="-form search">
                <div class="input -pad -center__flex">
                    <input type="text" class="-input" name="q">
                </div>
                <div class="send -pad -center__flex">
                    <button class="-button">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M380-320q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l224 224q11 11 11 28t-11 28q-11 11-28 11t-28-11L532-372q-30 24-69 38t-83 14Zm0-80q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>
                    </button>
                </div>
            </form>
            <a href="login/" class="-a login tab -center__flex">
                <div class="-iconlabel">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M520-120q-17 0-28.5-11.5T480-160q0-17 11.5-28.5T520-200h240v-560H520q-17 0-28.5-11.5T480-800q0-17 11.5-28.5T520-840h240q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H520Zm-73-320H160q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520h287l-75-75q-11-11-11-27t11-28q11-12 28-12.5t29 11.5l143 143q12 12 12 28t-12 28L429-309q-12 12-28.5 11.5T372-310q-11-12-10.5-28.5T373-366l74-74Z"/></svg>
                    </div>
                    <div class="label">
                        Login
                    </div>
                </div>
            </a>
        </div>
    HTML;
}

function renderNavigation() {
    return <<<HTML
        <div class="-navigation">
            <a href="login/" class="-a profile -center__flex">
                <button class="-button">
                    <div class="-iconlabel">
                        <div class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M520-120q-17 0-28.5-11.5T480-160q0-17 11.5-28.5T520-200h240v-560H520q-17 0-28.5-11.5T480-800q0-17 11.5-28.5T520-840h240q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H520Zm-73-320H160q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520h287l-75-75q-11-11-11-27t11-28q11-12 28-12.5t29 11.5l143 143q12 12 12 28t-12 28L429-309q-12 12-28.5 11.5T372-310q-11-12-10.5-28.5T373-366l74-74Z"/></svg>
                        </div>
                        <div class="label">
                            Login
                        </div>
                    </div>
                </button>
            </a>
            <a href="./" class="-a home tab -pad">
                <div class="-iconlabel">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M160-200v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H600q-17 0-28.5-11.5T560-160v-200q0-17-11.5-28.5T520-400h-80q-17 0-28.5 11.5T400-360v200q0 17-11.5 28.5T360-120H240q-33 0-56.5-23.5T160-200Z"/></svg>
                    </div>
                    <div class="label">
                        Home
                    </div>
                </div>
            </a>
            <a href="explore/" class="-a explore tab -pad">
                <div class="-iconlabel">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M400-160q-33 0-56.5-23.5T320-240q0-33 23.5-56.5T400-320h400q33 0 56.5 23.5T880-240q0 33-23.5 56.5T800-160H400Zm0-240q-33 0-56.5-23.5T320-480q0-33 23.5-56.5T400-560h400q33 0 56.5 23.5T880-480q0 33-23.5 56.5T800-400H400Zm0-240q-33 0-56.5-23.5T320-720q0-33 23.5-56.5T400-800h400q33 0 56.5 23.5T880-720q0 33-23.5 56.5T800-640H400Zm-240 0q-33 0-56.5-23.5T80-720q0-33 23.5-56.5T160-800q33 0 56.5 23.5T240-720q0 33-23.5 56.5T160-640Zm0 240q-33 0-56.5-23.5T80-480q0-33 23.5-56.5T160-560q33 0 56.5 23.5T240-480q0 33-23.5 56.5T160-400Zm0 240q-33 0-56.5-23.5T80-240q0-33 23.5-56.5T160-320q33 0 56.5 23.5T240-240q0 33-23.5 56.5T160-160Z"/></svg>
                    </div>
                    <div class="label">
                        Explore
                    </div>
                </div>
            </a>
            <a href="archive/" class="-a archive tab -pad">
                <div class="-iconlabel">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M200-80q-33 0-56.5-23.5T120-160v-451q-18-11-29-28.5T80-680v-120q0-33 23.5-56.5T160-880h640q33 0 56.5 23.5T880-800v120q0 23-11 40.5T840-611v451q0 33-23.5 56.5T760-80H200Zm-40-600h640v-120H160v120Zm240 280h160q17 0 28.5-11.5T600-440q0-17-11.5-28.5T560-480H400q-17 0-28.5 11.5T360-440q0 17 11.5 28.5T400-400Z"/></svg>
                    </div>
                    <div class="label">
                        Archive
                    </div>
                </div>
            </a>
            <a href="forum/" class="-a forum tab -pad">
                <div class="-iconlabel">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M840-136q-8 0-15-3t-13-9l-92-92H320q-33 0-56.5-23.5T240-320v-40h440q33 0 56.5-23.5T760-440v-280h40q33 0 56.5 23.5T880-640v463q0 18-12 29.5T840-136ZM120-336q-16 0-28-11.5T80-377v-423q0-33 23.5-56.5T160-880h440q33 0 56.5 23.5T680-800v280q0 33-23.5 56.5T600-440H240l-92 92q-6 6-13 9t-15 3Z"/></svg>
                    </div>
                    <div class="label">
                        Forum
                    </div>
                </div>
            </a>
            <a href="settings/" class="-a settings tab -pad">
                <div class="-iconlabel">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M433-80q-27 0-46.5-18T363-142l-9-66q-13-5-24.5-12T307-235l-62 26q-25 11-50 2t-39-32l-47-82q-14-23-8-49t27-43l53-40q-1-7-1-13.5v-27q0-6.5 1-13.5l-53-40q-21-17-27-43t8-49l47-82q14-23 39-32t50 2l62 26q11-8 23-15t24-12l9-66q4-26 23.5-44t46.5-18h94q27 0 46.5 18t23.5 44l9 66q13 5 24.5 12t22.5 15l62-26q25-11 50-2t39 32l47 82q14 23 8 49t-27 43l-53 40q1 7 1 13.5v27q0 6.5-2 13.5l53 40q21 17 27 43t-8 49l-48 82q-14 23-39 32t-50-2l-60-26q-11 8-23 15t-24 12l-9 66q-4 26-23.5 44T527-80h-94Zm49-260q58 0 99-41t41-99q0-58-41-99t-99-41q-59 0-99.5 41T342-480q0 58 40.5 99t99.5 41Z"/></svg>
                    </div>
                    <div class="label">
                        Settings
                    </div>
                </div>
            </a>
            <a href="help/" class="-a help tab -pad">
                <div class="-iconlabel">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm2 160q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm4-572q25 0 43.5 16t18.5 40q0 22-13.5 39T502-525q-23 20-40.5 44T444-427q0 14 10.5 23.5T479-394q15 0 25.5-10t13.5-25q4-21 18-37.5t30-31.5q23-22 39.5-48t16.5-58q0-51-41.5-83.5T484-720q-38 0-72.5 16T359-655q-7 12-4.5 25.5T368-609q14 8 29 5t25-17q11-15 27.5-23t34.5-8Z"/></svg>
                    </div>
                    <div class="label">
                        Help
                    </div>
                </div>
            </a>
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
function sendCurl($url, $method, $headers, $data) {
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
function alert($message, $redirect = "") {
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