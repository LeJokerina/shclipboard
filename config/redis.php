<?php
$redis=new Redis();
$host="172.20.0.2";
$port=6379;
$redis->connect($host, $port);
function getUserIdFromSessionToken($sessionToken) {
    global $redis;
    return $redis->get("session:$sessionToken");
}

function validateSessionToken($sessionToken) {
    global $redis;
    if (isset($sessionToken)) {
        if ($redis->exists("session:$sessionToken")){
            return true;

        }
    }
    
    return false;
}

function storeSessionToken($sessionToken, $uid) {
    global $redis;
    $redis->set("session:$sessionToken", $uid);
    $redis->expire("session:$sessionToken", 86400);
}

function deleteSessionToken($sessionToken) {
    global $redis;
    $redis->del("session:$sessionToken");
}
?>