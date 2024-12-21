<?php

chdir("../");
include("common.php");
Debug();
$data = GetSiteData();
$user = GetUserData();
$requestIndex = FindIndex($data["requests"], "id", $_GET["id"]);

if ($requestIndex == -1) {
    Alert("The request does not exist.");
}

$request = $data["requests"][$requestIndex];
$tagList = explode(" ", $request["tags"]);
$title = "";
$i = 0;

foreach ($tagList as $tag) {
    if ($i > 3) {
        break;
    }

    $title .= "{$tag}, ";
    $i++;
}

$title = substr($title, 0, -2);

?>

<html>
    <head>
        <title>
            <?=$title?> | SaucePls
        </title>
        <base href="../">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>

        </style>
    </head>
    <body>
        <div class="main__request">
            <?=SetHeader()?>
            <div class="content">

            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>
        
    </script>
</html>