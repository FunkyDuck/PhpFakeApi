<?php
header('Content-Type: application/json');
echo json_encode([
    'server' => $_SERVER['SERVER_SOFTWARE'],
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
]);
