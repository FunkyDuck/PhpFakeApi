<?php
ini_set('display_errors','0');

require_once __DIR__ . "/../src/libraries/lib_db.php";
require_once __DIR__ . "/../src/Constants.php";

use Database\JsonDb as Db;
use App\Constants;

error_log("PHP Fake API version " . Constants::VERSION . "\nCreated by Ginji@FunkyDuck.");

header('Content-Type: application/json');
    
$method = $_SERVER["REQUEST_METHOD"];
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/");
$segments = explode("/", $uri);

$db = new Db();

if(empty($segments[0])) {
    http_response_code(400);
    echo json_encode(["error" => "No path specified"]);
    exit;
}

switch($method) {
    case "GET":
        $db->handleGet($segments);
        break;

    case "POST":
        $db->handlePost($segments);
        break;

    case "PUT":
        $db->handlePut($segments);
        break;

    case "DELETE":
        $db->handleDelete($segments);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
