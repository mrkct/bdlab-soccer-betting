<?php
require_once('config.php');
require_once(LIB . '/database.php');
require_once(LIB . '/models/exceptions/DBException.php');
require_once(LIB . '/models/loggeduser.php');
require_once(LIB . '/models/exceptions/Util.php');


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
                'Player_find',
                'SELECT id, name, birthday, height, weight FROM player WHERE id = $1;'
            );
            pg_prepare(
                $db,
                'Player_insert',
                'SELECT 
                    id, name, birthday, height, weight, success, error_code, message 
                 FROM insert_player($1, $2, $3, $4, $5, $6);'
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
        $result = @pg_execute($db, 'Player_find', array($id));
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
     * Returns an associative array with the newly inserted
     * player on success, raises an exception if 
     * an error occurs. Exception that can be thrown are:
     * DuplicateDataException, PermissionDeniedException, DBException
     */
    public static function insert($db, $id, $name, $birthday, $height, $weight){
        $result = @pg_execute(
            $db, 
            'Player_insert', 
            array(LoggedUser::getId(), $id, $name, $birthday, $height, $weight)
        );
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }

        $row = pg_fetch_assoc($result);
        result_row_to_exception($row);

        return array(
            "id" => $row["id"],
            "name" => $row["name"],
            "birthday" => $row["birthday"],
            "height" => $row["height"],
            "weight" => $row["weight"]
        );
    }
}