<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
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
    if (!$request || !isset($request['action']) ||
    !isset($_COOKIE['sessionToken'])) {
        sendResponse(['error' => 'Invalid request'], 400);
    } 

    $sessionToken = $_COOKIE['sessionToken'];
    if(!validateSessionToken($sessionToken)) {
        sendResponse(['message'=>'Invalide token'],200);
    }



    $uId = getUserIdFromSessionToken($sessionToken);
    switch ($request['action']) {
        case 'create':
            handleCreate($uId, $request['name'], $request['content']);
            break;
        case 'update':
            handleUpdate($uId, $request['name'], $request['content']);
            break;
        case 'delete':
            handleDelete($uId, $request['name']);
            break;
        case 'fetch':
            handleFetch($uId, $request['name']);
            break;
        case 'fetchall':
            handleFetchAll($uId);
            break;
        default:
            sendResponse(['error'=>'Unknown action'], 400);    
               
    }
}

function handleCreate($uId, $name, $content) {
    if (!isset($name, $content)) {
        sendResponse(['error'=>'Invalid request'], 400);
    }   
    
    try{
        sendResponse(['status'=>createClip($uId, $name, $content)], 200);

    } catch(Exception $e){
        sendResponse(['error'=>'Message: '.$e->getMessage()]);
    }

}

function handleUpdate($uId, $name, $content) {
    if (!isset($name, $content)){
        sendResponse(['error'=> 'Invalid request'], 400);

    }

    try{
        sendResponse(['status'=>updateClip($uId, $name, $content)], 200);
    }   catch(Exception $e) {
        sendResponse(['error'=>'Message:' .$e->getMessage()]);
    }
}

function handleDelete($uId, $name) {
    if (!isset($uId, $name)) {
        sendResponse(['error'=>'Invalid request'], 400);
    }   
    
    try {
        sendResponse(['status'=>deleteClip($uId, $name)],200);
    } catch(Exception $e) {
        sendResponse(['error'=>'Message:' .$e->getMessage()]);

    }
}

function handleFetch($uId, $name) {
    if (!isset($uId) || !isset($name)) {

        sendResponse(['error'=> 'Invalid request'], 400);
    }
    
    try {
        $data = fetchClip($uId, $name);
        if (!empty($data)) {
            sendResponse($data, 200);
        } else {
            sendResponse(['clipcount'=>0], 200);
        }
    } catch (Exception $e) {
        sendResponse(['error'=>'Message:' . $e->getMessage()], 500);
    
    }
}

function handleFetchAll($uId) {
    if(!isset($uId)) {
        sendResponse(['error'=> 'Invalid request'], 400);
    }

    try {
        $data = fetchClips($uId);
        if (!empty($data)) {
            sendResponse(['clips'=>$data], 200);
        } else {
            sendResponse(['clipcount'=>0], 200);
        }
    } catch(Exception $e) {
        sendResponse(['error'=>'Message:' . $e->getMessage()], 500);
    }
}
?>