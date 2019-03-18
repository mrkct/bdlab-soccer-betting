<?php
require_once('config.php');
require_once(LIB . '/database.php');
require_once(LIB . '/models/exceptions/DBException.php');
require_once(LIB . '/models/exceptions/DuplicateDataException.php');
require_once(LIB . '/models/exceptions/PermissionDeniedException.php');
require_once(LIB . '/models/loggeduser.php');
require_once(LIB . '/models/exceptions/Util.php');


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
                'SELECT id, name, success, error_code, message 
                 FROM insert_bet_provider($1, $2, $3);'
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
     * Returns an associative array with the newly inserted
     * bet provider on success, raises an exception if 
     * an error occurs. Exception that can be thrown are:
     * DuplicateDataException, PermissionDeniedException, DBException
     */
    public static function insert($db, $id, $name){
        $result = @pg_execute(
            $db, 
            'BetProvider_insert', 
            array(LoggedUser::getId(), $id, $name)
        );
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }
        $row = pg_fetch_assoc($result);
        result_row_to_exception($row);

        return array(
            "id" => $row["id"],
            "name" => $row["name"]
        );
    }
}