<?php
ini_set('display_errors', '0');
header("Access-Control-Allow-Origin: */*, *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$isBrowser = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') != false;

if(!$isBrowser) {
    require_once __DIR__ . "/public/index.php";
    exit;
}

$dataDir = __DIR__ . "/data";
$files = glob($dataDir . "/*.json");

$collections = array_filter(array_map(function($file) {
    return preg_match("/-schema\.json$/", $file) ? null : basename($file, '.json');
}, $files));

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>HTTPotamus • PHP Fake API</title>
        <style>
            body {
                font-family: sans-serif;
                margin: 1dvh 2dvw;
                background: #f7f7f7;
            }

            h1 {
                color: #444444;
            }

            ul {
                line-height: 1.6;
            }

            a {
                color: #007acc;
                text-decoration: none;
            }

            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>

    <body>
        <h1>HTTPotamus • PHP Fake API</h1>
        <p>the following API collections are currently available :</p>
        <ul>
            <?php foreach ($collections as $c) { 
                echo '<li><a href="/<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></a></li>';
            } ?>
        </ul>
        <p><em>This page is shown because the request was made from a browser.</em></p>
    </body>
</html>