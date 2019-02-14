<?php
require_once(LIB . '/database.php');
require_once('dbexception.php');
require_once('config.php');


class BetProvider{
    /**
     * Prepares the queries for the other functions. This needs
     * to be called first, before any other method. This has effect only
     * the first time it is called.
     */
    public static function prepare($db){
        static $prepared = false;
        if( !$prepared ){
            pg_prepare(
                $db,
                'BetProvider_find',
                'SELECT id, name FROM bet_provider WHERE id = $1;'
            );
            pg_prepare(
                $db,
                'BetProvider_insert',
                'INSERT INTO bet_provider(id, name) VALUES ($1, $2) RETURNING id, name;'
            );
            $prepared = true;
        }
    }

    /**
     * Returns a bet provider found by its' id.
     * Returns NULL if not found, raises an
     * exception if an error occurs
     */
    public static function find($db, $id){
        $result = @pg_execute($db, 'BetProvider_find', array($id));
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }

        if( ($row = pg_fetch_assoc($result)) != false ){
            return $row;
        } else {
            return NULL;
        }
    }
    /**
     * Inserts a bet provider in the database.
     * Returns the inserted bet provider if success, NULL if the database does
     * not support the RETURNING construct and can't return after an INSERT.
     * Raises an exception if an error occurs.
     */
    public static function insert($db, $id, $name){
        $result = @pg_execute(
            $db, 
            'BetProvider_insert', 
            array($id, $name)
        );
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }
        
        if( ($row = pg_fetch_assoc($result)) != false ){
            return $row;
        } else {
            return NULL;
        }
    }
}