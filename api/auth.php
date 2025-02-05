<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo "403 Forbidden";
    exit();
}

require '../config/database.php'; 
require '../config/redis.php'; 

handlePost();

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type:application/json');
    echo json_encode($data);
    exit();
}

function handlePost() {
    $request = json_decode(file_get_contents('php://input'), true);
    if (!$request || !isset($request['action'])) {
        sendResponse(['error' => 'Invalid request'], 400);
    } 

    switch ($request['action']) {
        case 'login':
            handleLogin($request);
            break;
        case 'logout':
            handleLogout();
            break;
        case 'fetchUsername':
            fetchUsername($_COOKIE['sessionToken']);
            break;
        default:
            sendResponse(['error' => 'Unknown action'], 400);
    }
}

function handleLogin($request) {
    if(!isset($request['username'], $request['password'])) {
        sendResponse(['error' => 'Invalid request'], 400);
    }

    $userDetails = fetchUserDetails($request['username']);

    if(!$userDetails || hash('sha512', $request['password']) !== $userDetails['password']) {
        sendResponse(['error' => 'Invalid username or password'], 401);
    }

    $sessionToken = bin2hex(random_bytes(32));
    storeSessionToken($sessionToken, $userDetails['uid']);
    initiateSession($sessionToken);
    sendResponse(['message' => "Login successful"]);
}

function handleLogout() {
    if(!isset($_COOKIE['sessionToken'])) {
        sendResponse(['error' => 'Invalid request'], 400);
    }

    deleteSessionToken($_COOKIE['sessionToken']);
    sendResponse(['message' => 'Logout successful']);
}

function initiateSession($sessionToken) {
    setcookie('sessionToken', $sessionToken, [
        'expires' => time() + 86400,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

function fetchUsername($sessionToken) {
    if(!validateSessionToken($sessionToken)) {
        sendResponse(['message' => 'Invalid token'], 200);
    }

    $uid = getUserIdFromSessionToken($sessionToken);
    sendResponse(getUsernameFromUid($uid), 200);
}


?>


