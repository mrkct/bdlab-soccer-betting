<?php
require_once('database.php');

/**
 * Checks if a pair of name and password is a valid user
 * or not. If it is valid it returns the DB row from that,
 * otherwise returns false
 * @param name: Name of the user to check the login
 * @param password: Password of the user, un-hashed
 */
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

/**
 * Stores all the user rows in $_SESSION, except for the
 * password. This does NOT check if the data is valid, use
 * verify_login to do that.
 * @param id: ID of the user to login
 * @param name: Name of the user to login
 * @param role: Role of the user to login
 * @param affiliation: Affiliation of the user to login
 */
function save_login($id, $name, $role, $affiliation){
    if( session_status() == PHP_SESSION_NONE ){
        session_start();
    }
    $_SESSION['logged'] = true;
    $_SESSION['name'] = $name;
    $_SESSION['id'] = $id;
    $_SESSION['role'] = $role;
    $_SESSION['affiliation'] = $affiliation;
}

/**
 * Unsets all the login related data in $_SESSION, except
 * for 'logged' which is set to false.
 */
function logout(){
    if( session_status() == PHP_SESSION_NONE ){
        session_start();
    }
    unset($_SESSION['name']);
    unset($_SESSION['id']);
    unset($_SESSION['name']);
    unset($_SESSION['role']);
    unset($_SESSION['affiliation']);
    $_SESSION['logged'] = false;
}