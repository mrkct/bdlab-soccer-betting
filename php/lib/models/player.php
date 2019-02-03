<?php
require_once(LIB . '/database.php');
require_once('dbexception.php');
require_once('config.php');


class Player{
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
                'find_player',
                'SELECT id, name, birthday, height, weight FROM player WHERE id = $1;'
            );
            pg_prepare(
                $db,
                'insert_player',
                'INSERT INTO player(id, name, birthday, height, weight) VALUES ($1, $2, $3, $4, $5);'
            );
            $prepared = true;
        }
    }
    /**
     * Returns a player found by its' id.
     * Returns NULL if not found, raises an
     * exception if an error occurs
     */
    public static function find($db, $id){
        $result = @pg_execute($db, 'find_player', array($id));
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
     * Inserts a player in the database.
     * Returns true if success, raises an exception if
     * an error occurs.
     */
    public static function insert($db, $id, $name, $birthday, $height, $weight){
        $result = @pg_execute(
            $db, 
            'insert_player', 
            array($id, $name, $birthday, $height, $weight)
        );
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }

        return true;
    }
}