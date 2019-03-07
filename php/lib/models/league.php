<?php
require_once('config.php');
require_once(LIB . '/database.php');
require_once(LIB . '/models/exceptions/DBException.php');
require_once(LIB . '/models/exceptions/DuplicateDataException.php');
require_once(LIB . '/models/exceptions/PermissionDeniedException.php');
require_once(LIB . '/models/loggeduser.php');
require_once(LIB . '/models/exceptions/Util.php');


class League{
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
                'League_findByName',
                'SELECT id, name, country FROM league WHERE name = $1;'
            );
            pg_prepare(
                $db,
                'League_findById',
                'SELECT id, name, country FROM league WHERE id = $1;'
            );
            pg_prepare(
                $db,
                'League_findByNameAndCountry',
                'SELECT id, name, country FROM league WHERE name = $1 AND country = $2;'
            );
            pg_prepare(
                $db,
                'League_insert',
                'SELECT success, error_code, message FROM insert_league($1, $2, $3, $4);'
            );
            $prepared = true;
        }
    }

    /**
     * Returns a league found by its' id.
     * Returns NULL if not found, raises an
     * exception if an error occurs
     */
    public static function findById($db, $id){
        $result = @pg_execute($db, 'League_findById', array($id));
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
     * Returns a league found by its' name.
     * Returns NULL if not found, raises an
     * exception if an error occurs
     */
    public static function findByName($db, $name){
        $result = @pg_execute($db, 'League_findByName', array($name));
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }

        if( ($row = pg_fetch_assoc($result)) != false ){
            return $row;
        } else {
            return NULL;
        }
    }

    public static function findByNameAndCountry($db, $name, $country){
        $result = @pg_execute($db, 'League_findByNameAndCountry', array($name, $country));
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
     * Inserts a league in the database.
     * Returns true if success, raises an exception if 
     * an error occurs. Exception that can be thrown are:
     * - DBException: A query error occurred. See the message
     * for more info
     */
    public static function insert($db, $name, $country){
        $result = @pg_execute(
            $db, 
            'League_insert', 
            array(LoggedUser::getId(), NULL, $name, $country)
        );
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }
        $row = pg_fetch_assoc($result);
        result_row_to_exception($row);

        return true;
    }
}