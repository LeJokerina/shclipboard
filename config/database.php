<?php
function getConnection() {
    $host="172.20.0.3";
    $username="admin";
    $port="5432";
    $dbname="shclipboard";
    $password="strongpassword";
    $dsn="pgsql:host=$host;port=$port;dbname=$dbname";
    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}

/**
 * @param int $uid
 * @return array|null
 */
function getUsernameFromUid($uid) {
    $db=getConnection();
    $stmt=$db->prepare("SELECT username FROM users WHERE uid= :uid");
    $stmt->execute(['uid'=>$uid]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * @param string $usrename
 * @return array|null
 */
function fetchUserDetails ($username){
    $db=getConnection();
    $stmt=$db->prepare("SELECT uId, password FROM users WHERE username=:username");
    $stmt->execute(['username'=>$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * @param int $uId
 * @param string $name
 * @param string $content
 * @return bool
 */
function createClip($uId, $name, $content){
    $db= getConnection();
    $stmt= $db->prepare("INSERT INTO clips (uId, name, content) VALUES (:uid, :name, :content)");
    return $stmt->execute(['uid'=>$uId, 'name'=>$name, 'content'=>$content]);
}

/**
 * @param int $uId
 * @param string $name
 * @param string $content
 * @return bool
 */
function updateClip($uId, $name, $content){
    $db=getConnection();
    $stmt=$db->prepare("UPDATE clips SET content=:content, updated=CURRENT_TIMESTAMP WHERE uId=:uid AND name=:name");
    return $stmt->execute(['uid'=>$uId, 'name'=>$name,'content'=>$content]);
}

/**
* @param int $uId Die Benutzer-ID.
* @param string $name Der Name des Clips.
* @return bool
*/
function deleteClip($uId, $name){
    $db=getConnection();
    $stmt=$db->prepare("DELETE FROM clips WHERE name =:name AND uID=:uid");
    return$stmt->execute(['uid'=>$uId, 'name'=>$name]);
}

/**
 * Summary of fetchClip
 * @param int $uId
 * @param string $name
 * @return array|null
 */
function fetchClip($uId, $name){
    $db=getConnection();
    $stmt=$db->prepare("SELECT content FROM clips WHERE uId= :uid AND name= :name");
    $stmt->execute(['uid'=>$uId, 'name'=> $name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Summary of fetchClips
 * @param int $uId
 * @return array|bool
 */
function fetchClips($uId){
    $db =getConnection();
    $stmt=$db->prepare("SELECT name FROM clips WHERE uID=:uid ORDER BY updated DESC");
    $stmt->execute(['uid'=>$uId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
 ?>