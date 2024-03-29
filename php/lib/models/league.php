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
                'SELECT id, name, country, success, error_code, message 
                 FROM insert_league($1, $2, $3, $4);'
            );
            pg_prepare(
                $db,
                'League_delete',
                'SELECT * FROM delete_league($1, $2);'
            );
            pg_prepare(
                $db,
                'League_edit',
                'SELECT * FROM edit_league($1, $2, $3, $4, $5);'
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
     * Returns an associative array with the newly inserted
     * league on success, raises an exception if 
     * an error occurs. Exception that can be thrown are:
     * DuplicateDataException, PermissionDeniedException, DBException
     */
    public static function insert($db, $name, $country){
        $row = execute_query($db, 'League_insert', array(LoggedUser::getId(), NULL, $name, $country));
        return League::rowToArray($row);
    }

    /**
     * Deletes a league and all related db rows in other tables
     * by its id. Returns the deleted row on success, raises an
     * exception on failure.
     */
    public static function delete($db, $id){
        $row = execute_query($db, 'League_delete', array(LoggedUser::getId(), $id));
        return League::rowToArray($row);
    }

    /**
     * Edits a league row based on the passed id.
     * Returns the newly edited row on success. Throws an
     * exception on failure
     * @param db: A valid database connection
     * @param id: The id of the row to edit
     * @param new_id: New id value of the row
     * @param new_name: New name value of the row
     * @param new_country: New country value of the row
     */
    public static function edit($db, $id, $new_id, $new_name, $new_country){
        $row = execute_query($db, 'League_edit',  array(LoggedUser::getId(), $id, $new_id, $new_name, $new_country));
        return League::rowToArray($row);
    }

    /**
     * Takes an associative array for a database row
     * and returns another associative array with all
     * the useless parameters filtered
     */
    private static function rowToArray($row){
        return array(
            "id" => $row["id"],
            "name" => $row["name"],
            "country" => $row["country"]
        );
    }
}