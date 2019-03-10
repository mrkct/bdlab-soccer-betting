<?php
require_once('config.php');

function db_connect(){
    $connection_string = sprintf(
        'host=%s port=%d dbname=%s user=%s password=%s', 
        DB_HOST, 
        DB_PORT, 
        DB_NAME, 
        DB_USER, 
        DB_PASSWORD
    );

    static $db = null;
    if( $db == null ){
        $db = pg_connect($connection_string);
        pg_query(
            $db, 
            sprintf("SET search_path=%s;", DB_SCHEMA)
        );
    }

    return $db;
}