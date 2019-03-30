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
                'SELECT * FROM insert_team($1, $2, $3, $4);'
            );
            pg_prepare(
                $db,
                'Team_delete',
                'SELECT * FROM delete_team($1, $2);'
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
     * Returns an associative array with the newly inserted
     * team on success, raises an exception if 
     * an error occurs. Exception that can be thrown are:
     * DuplicateDataException, PermissionDeniedException, 
     * DBException
     */
    public static function insert($db, $id, $shortname, $longname){
        $row = execute_query(
            $db, 
            'Team_insert', 
            array(LoggedUser::getId(), $id, $shortname, $longname)
        );

        return Team::rowToArray($row);
    }

    /**
     * Deletes the team row with the passed id
     * and returns it on success. Throws an exception
     * on failure.
     */
    public static function delete($db, $id){
        $row = execute_query($db, 'Team_insert', array(LoggedUser::getId(), $id));
        return Team::rowToArray($row);
    }

    /**
     * Takes an associative array for a database row
     * and returns another associative array with all
     * the useless parameters filtered
     */
    private static function rowToArray($row){
        return array(
            "id" => $row["id"],
            "shortname" => $row["shortname"],
            "longname" => $row["longname"]
        );
    }
}