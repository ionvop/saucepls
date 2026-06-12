<?php

header("Content-Type: application/json");

if (isset($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"] != "localhost:8000") {
    echo "Access denied. This page is only available on localhost.";
    exit;
}

if (isset($_GET["method"])) {
    switch ($_GET["method"]) {
        case "login":
            login();
            break;
        default:
            defaultMethod();
            break;
    }
} else {
    defaultMethod();
}

function login() {
    $db = new SQLite3("database.db");
    $session = uniqid("session-");

    $query = <<<SQL
        UPDATE `users` SET `session` = :session WHERE `username` = :username
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":session", $session);
    $stmt->bindValue(":username", $_GET["u"]);
    $stmt->execute();
    setcookie("session", $session, time() + (86400 * 30));
    header("Location: ./");
}

function defaultMethod() {
    echo "No method specified.";
}

?>