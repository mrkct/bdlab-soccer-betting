<?php

/**
 * A helper class for the data in the $_SESSION superglobal
 * for an already logged in user
 */
class LoggedUser{
    /**
     * Starts the session if it wasn't already started
     */
    private static function sessionPrepare(){
        if( session_status() == PHP_SESSION_NONE ){
            session_start();
        }
    }

    /**
     * Returns the argument key in the $_SESSION superglobal
     * if it is set, returns NULL otherwise
     */
    private static function getKey($key){
        if( isset($_SESSION[$key]) ){
            return $_SESSION[$key];
        } else {
            return NULL;
        }
    }

    /**
     * Returns the logged user id or NULL if the
     * user is not logged
     */
    public static function getId(){
        LoggedUser::sessionPrepare();
        return LoggedUser::getKey('id');
    }

    /**
     * Returns the logged user's name or NULL if the
     * user is not logged
     */
    public static function getName(){
        LoggedUser::sessionPrepare();
        return LoggedUser::getKey('name');
    }

    /**
     * Returns the logged user's role of NULL if the
     * user is not logged
     */
    public static function getRole(){
        LoggedUser::sessionPrepare();
        return LoggedUser::getKey('role');
    }
}