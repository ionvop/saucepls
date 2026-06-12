<?php

require_once "config.php";

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

function renderNavigation() {
    $user = getUser();
    $link = "login/";
    $avatar = "assets/image.png";
    $username = "Guest";
    
    $login = <<<HTML
        <a style="
            display: grid;
            grid-template-columns: max-content 1fr;
            border-bottom: 1px solid #111;"
            class="-tab"
            href="login/">
            <div style="
                display: flex;
                align-items: center;
                padding: 1rem;">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M520-120q-17 0-28.5-11.5T480-160q0-17 11.5-28.5T520-200h240v-560H520q-17 0-28.5-11.5T480-800q0-17 11.5-28.5T520-840h240q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H520Zm-73-320H160q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520h287l-75-75q-11-11-11-27t11-28q11-12 28-12.5t29 11.5l143 143q12 12 12 28t-12 28L429-309q-12 12-28.5 11.5T372-310q-11-12-10.5-28.5T373-366l74-74Z"/></svg>
            </div>
            <div style="
                display: flex;
                align-items: center;
                padding: 1rem;
                padding-left: 0rem;">
                Login
            </div>
        </a>
    HTML;

    if ($user != false) {
        $link = "user/?id={$user['id']}";
        $avatar = "uploads/avatars/{$user['avatar']}";
        $username = htmlentities($user["username"]);

        $login = <<<HTML
            <form style="
                display: grid;
                grid-template-columns: max-content 1fr;
                border-bottom: 1px solid #111;"
                class="-tab"
                href="login/"
                action="server.php"
                method="post"
                enctype="multipart/form-data"
                onclick="if (confirm('Are you sure you want to logout?')) this.submit()">
                <div style="
                    display: flex;
                    align-items: center;
                    padding: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h240q17 0 28.5 11.5T480-800q0 17-11.5 28.5T440-760H200v560h240q17 0 28.5 11.5T480-160q0 17-11.5 28.5T440-120H200Zm487-320H400q-17 0-28.5-11.5T360-480q0-17 11.5-28.5T400-520h287l-75-75q-11-11-11-27t11-28q11-12 28-12.5t29 11.5l143 143q12 12 12 28t-12 28L669-309q-12 12-28.5 11.5T612-310q-11-12-10.5-28.5T613-366l74-74Z"/></svg>
                </div>
                <div style="
                    display: flex;
                    align-items: center;
                    padding: 1rem;
                    padding-left: 0rem;">
                    Logout
                </div>
                <input type="hidden" name="method" value="logout">
            </form>
        HTML;
    }

    return <<<HTML
        <div style="
            border-right: 1px solid #555;
            background-color: #222;
            width: 15rem;">
            <a style="
                display: block;
                border-bottom: 1px solid #111;"
                href="{$link}">
                <div style="
                    padding: 3rem;
                    padding-bottom: 1rem;
                    text-align: center;">
                    <img style="
                        width: 5rem;
                        height: 5rem;
                        border-radius: 50%;
                        object-fit: cover;"
                        src="{$avatar}">
                </div>
                <div style="
                    padding: 1rem;
                    padding-top: 0rem;
                    padding-bottom: 3rem;
                    text-align: center;">
                    {$username}
                </div>
            </a>
            <a style="
                display: grid;
                grid-template-columns: max-content 1fr;
                border-bottom: 1px solid #111;"
                class="-tab"
                href="./">
                <div style="
                    display: flex;
                    align-items: center;
                    padding: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M160-200v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H600q-17 0-28.5-11.5T560-160v-200q0-17-11.5-28.5T520-400h-80q-17 0-28.5 11.5T400-360v200q0 17-11.5 28.5T360-120H240q-33 0-56.5-23.5T160-200Z"/></svg>
                </div>
                <div style="
                    display: flex;
                    align-items: center;
                    padding: 1rem;
                    padding-left: 0rem;">
                    Home
                </div>
            </a>
            <a style="
                display: grid;
                grid-template-columns: max-content 1fr;
                border-bottom: 1px solid #111;"
                class="-tab"
                href="about/">
                <div style="
                    display: flex;
                    align-items: center;
                    padding: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M508.5-291.5Q520-303 520-320v-160q0-17-11.5-28.5T480-520q-17 0-28.5 11.5T440-480v160q0 17 11.5 28.5T480-280q17 0 28.5-11.5Zm0-320Q520-623 520-640t-11.5-28.5Q497-680 480-680t-28.5 11.5Q440-657 440-640t11.5 28.5Q463-600 480-600t28.5-11.5ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>
                </div>
                <div style="
                    display: flex;
                    align-items: center;
                    padding: 1rem;
                    padding-left: 0rem;">
                    About
                </div>
            </a>
            <a style="
                display: grid;
                grid-template-columns: max-content 1fr;
                border-bottom: 1px solid #111;"
                class="-tab"
                href="contact/">
                <div style="
                    display: flex;
                    align-items: center;
                    padding: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M470-200h-10q-142 0-241-99t-99-241q0-142 99-241t241-99q71 0 132.5 26.5t108 73q46.5 46.5 73 108T800-540q0 134-75.5 249T534-111q-10 5-20 5.5t-18-4.5q-8-5-14-13t-7-19l-5-58Zm18-133q12-12 12-29t-12-29q-12-12-29-12t-29 12q-12 12-12 29t12 29q12 12 29 12t29-12ZM372-625q11 5 22 .5t18-14.5q9-12 21-18.5t27-6.5q24 0 39 13.5t15 34.5q0 13-7.5 26T480-558q-25 22-37 41.5T431-477q0 12 8.5 20.5T460-448q12 0 20-9t12-21q5-17 18-31t24-25q21-21 31.5-42t10.5-42q0-46-31.5-74T460-720q-32 0-59 15.5T357-662q-6 11-1.5 21.5T372-625Z"/></svg>
                </div>
                <div style="
                    display: flex;
                    align-items: center;
                    padding: 1rem;
                    padding-left: 0rem;">
                    Contact
                </div>
            </a>
            {$login}
        </div>
    HTML;
}

function renderHeader() {
    return <<<HTML
        <div style="
            display: grid;
            grid-template-columns: max-content 1fr max-content;
            background-color: var(--theme);">
            <a style="
                display: flex;
                align-items: center;
                padding: 1rem;
                font-size: 1.5rem;
                font-weight: bold;"
                href="./">
                SaucePls
            </a>
            <div></div>
            <div style="
                display: flex;
                align-items: center;
                padding: 1rem;">
                &copy; 2026 ionvop
            </div>
        </div>
    HTML;
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

    exit;
}

/**
 * Performs an HTTP request using cURL and returns the response details.
 *
 * This function supports GET, POST, PUT, DELETE, and other HTTP methods.
 * It allows setting custom headers, a request body (JSON or raw), and a timeout.
 * The response includes status code, success flag, parsed headers, raw body, and JSON-decoded data.
 *
 * @param string $url The URL to which the request is sent.
 * @param array $options Optional request configuration:
 *   - 'method'  (string): HTTP method to use (default: 'GET').
 *   - 'headers' (array): Associative array of request headers (e.g., ['Content-Type' => 'application/json']).
 *   - 'body'    (mixed): Request body, either a string or an array (JSON-encoded if Content-Type is application/json).
 *   - 'timeout' (int): Request timeout in seconds (default: 30).
 *
 * @return array An associative array containing:
 *   - 'status'  (int): The HTTP status code of the response.
 *   - 'ok'      (bool): True if the status code is in the 200–299 range, false otherwise.
 *   - 'headers' (array): Parsed response headers as an associative array.
 *   - 'body'    (string): Raw response body as a string.
 *   - 'json'    (mixed): JSON-decoded response body (associative array or null if not JSON or decoding fails).
 *
 * @throws RuntimeException If the cURL request fails or the URL is invalid.
 */
function fetch(string $url, array $options = []): array {
    $ch = curl_init();

    // Default options
    $method = strtoupper($options['method'] ?? 'GET');
    $headers = $options['headers'] ?? [];
    $body = $options['body'] ?? null;
    $timeout = $options['timeout'] ?? 30;

    // Configure cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    // Bypass SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Handle body (JSON or raw)
    if ($body !== null) {
        if (is_array($body) && (isset($headers['Content-Type']) && stripos($headers['Content-Type'], 'application/json') !== false)) {
            $body = json_encode($body);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    // Convert headers to correct format
    $formattedHeaders = [];
    foreach ($headers as $key => $value) {
        $formattedHeaders[] = "$key: $value";
    }
    if (!empty($formattedHeaders)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
    }

    // Capture headers and body
    curl_setopt($ch, CURLOPT_HEADER, true);

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $headerString = substr($response, 0, $headerSize);
    $bodyString = substr($response, $headerSize);

    // Parse headers into associative array
    $headersArray = [];
    foreach (explode("\r\n", trim($headerString)) as $i => $line) {
        if ($i === 0) continue; // Skip HTTP/1.1 200 OK line
        if (strpos($line, ': ') !== false) {
            list($key, $value) = explode(': ', $line, 2);
            $headersArray[$key] = $value;
        }
    }

    curl_close($ch);

    return [
        'status' => $status,
        'ok' => ($status >= 200 && $status < 300),
        'headers' => $headersArray,
        'body' => $bodyString,
        'json' => json_decode($bodyString, true)
    ];
}

function getUser() {
    $db = new SQLite3("database.db");

    if (isset($_COOKIE["session"]) == false) {
        return false;
    }

    $query = <<<SQL
        SELECT * FROM `users` WHERE `session` = :session
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":session", $_COOKIE["session"]);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user == false) {
        return false;
    }

    if ($user["session"] == null) {
        return false;
    }

    $query = <<<SQL
        UPDATE `users` SET `last_seen` = :last_seen WHERE `id` = :id
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":last_seen", time());
    $stmt->bindValue(":id", $user["id"]);
    $stmt->execute();
    return $user;
}

function printLog(string $message): void {
    file_put_contents("log.txt", $message . "\n", FILE_APPEND);
}