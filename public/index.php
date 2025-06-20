<?php
ini_set('display_errors','0');

require_once __DIR__ . "/../src/libraries/lib_db.php";
require_once __DIR__ . "/../src/Constants.php";

use Database\JsonDb as Db;
use App\Constants;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit;
}

error_log("PHP Fake API version " . Constants::VERSION . "\nCreated by Ginji@FunkyDuck.");
    
$method = $_SERVER["REQUEST_METHOD"];

// if($method == "OPTIONS") {
//     http_response_code(204);
//     exit;
// }

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/");
$segments = explode("/", $uri);

if(empty($segments[0])) {
    http_response_code(400);
    echo json_encode(["error" => "No path specified"]);
    exit;
}

try {
    $collection = $segments[0];
    $db = new Db($collection);
    $resources = (!empty($segments[1])) ? $segments[1] : null;
    
    switch($method) {
        case "GET":
            $db->handleGet($collection, $resources);
            break;
            
        case "POST":
            $db->handlePost($collection, $resources);
            break;
                
        case "PUT":
            $db->handlePut($collection, $resources);
            break;
                    
        case "DELETE":
            $db->handleDelete($collection, $resources);
            break;
                        
        case "OPTIONS":
            http_response_code(200);
            break;
                            
        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
    }            
}
catch (\RuntimeException $e) {
    http_response_code(404);
    echo json_encode(["error" => $e->getMessage()], JSON_PRETTY_PRINT);
}