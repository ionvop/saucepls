<?php

$db = new SQLite3("database.db");

if (php_sapi_name() != "cli") {
    echo "Access denied. This page is only available from the command line.";
    exit();
}

$query = <<<SQL
    CREATE TABLE "posts" (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `user_id` INTEGER REFERENCES `users`(`id`), image TEXT, `title` TEXT, `description` TEXT, `tags` TEXT, `text` TEXT, `status` TEXT DEFAULT 'unsolved', `modified` INTEGER DEFAULT (unixepoch()), `time` INTEGER DEFAULT (unixepoch()));
    CREATE TABLE `user_comments` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `user_id` INTEGER REFERENCES `users`(`id`), `target_id` INTEGER REFERENCES `users`(`id`), `content` TEXT, `time` INTEGER DEFAULT (unixepoch()));
    CREATE TABLE "users" (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `username` TEXT UNIQUE, `email` TEXT UNIQUE, description TEXT DEFAULT 'Hello, world!', avatar TEXT DEFAULT 'default.jpg', `type` TEXT DEFAULT 'member', `session` TEXT, last_seen INTEGER DEFAULT (unixepoch()), `time` INTEGER DEFAULT (unixepoch()));
SQL;