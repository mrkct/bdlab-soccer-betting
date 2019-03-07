<?php
require_once(LIB . '/database.php');
require_once(LIB . '/models/exceptions/DBException.php');
require_once(LIB . '/models/loggeduser.php');
require_once(LIB . '/models/exceptions/Util.php');
require_once('config.php');


class Team{
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
                'Team_find',
                'SELECT id, shortname, longname FROM team WHERE id = $1'
            );
            pg_prepare(
                $db, 
                'Team_insert',
                'SELECT success, error_code, message FROM insert_team($1, $2, $3, $4);'
            );
            $prepared = true;
        }
    }
    /**
     * Returns a team found by its' id.
     * Returns NULL if not found, raises an
     * exception if an error occurs
     */
    public static function find($db, $id){
        $result = @pg_execute($db, 'Team_find', array($id));
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
     * Inserts a team in the database.
     * Returns true on success, raises an exception if
     * an error occurs. Exceptions that can be thrown are:
     * - DuplicateDataException
     * - PermissionDeniedException
     * - DBException
     */
    public static function insert($db, $id, $shortname, $longname){
        $result = @pg_execute(
            $db, 
            'Team_insert', 
            array(LoggedUser::getId(), $id, $shortname, $longname)
        );
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }
        $row = pg_fetch_assoc($result);
        result_row_to_exception($row);

        return true;
    }
}