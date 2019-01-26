<?php
require_once('database.php');

function verify_login($name, $password){
    $db = db_connect();
    pg_prepare(
        $db, 
        'get_user',
        'SELECT * FROM collaborator WHERE name = $1;'
    );
    $result = pg_execute($db, 'get_user', array($name));
    $row = pg_fetch_assoc($result);
    
    if( isset($row['password']) && 
        password_verify($password, $row['password']) ){
            return $row;
        } else {
            return false;
        }
}