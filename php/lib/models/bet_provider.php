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
            pg_prepare(
                $db,
                'BetProvider_delete',
                'SELECT * FROM delete_bet_provider($1, $2);'
            );
            pg_prepare(
                $db,
                'BetProvider_edit',
                'SELECT * FROM edit_bet_provider($1, $2, $3, $4);'
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

        return BetProvider::rowToArray($row);
    }

    /**
     * Deletes the bet provider row with the passed id
     * and returns it on success. Throws an exception
     * on failure.
     */
    public static function delete($db, $id){
        $row = execute_query(
            $db,
            'BetProvider_delete',
            array(LoggedUser::getId(), $id)
        );

        return BetProvider::rowToArray($row);
    }

    /**
     * Edits a bet provider row based on the passed id.
     * Returns the newly edited row on success. Throws an
     * exception on failure
     * @param db: A valid database connection
     * @param id: The id of the row to edit
     * @param new_id: New id value of the row
     * @param new_name: New name value of the row
     */
    public static function edit($db, $id, $new_id, $new_name){
        $row = execute_query(
            $db,
            'BetProvider_edit',
            array(LoggedUser::getId(), $id, $new_id, $new_name)
        );

        return BetProvider::rowToArray($row);
    }

    /**
     * Takes an associative array for a database row
     * and returns another associative array with all
     * the useless parameters filtered
     */
    private static function rowToArray($row){
        return array(
            "id" => $row["id"],
            "name" => $row["name"]
        );
    }
}