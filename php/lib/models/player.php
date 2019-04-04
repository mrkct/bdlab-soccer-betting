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
            pg_prepare(
                $db,
                'Player_delete',
                'SELECT * FROM delete_player($1, $2);'
            );
            pg_prepare(
                $db,
                'Player_edit',
                "SELECT * FROM edit_player($1, $2, $3, $4, $5, $6, $7);"
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
        $row = execute_query(
            $db, 
            'Player_insert', 
            array(LoggedUser::getId(), $id, $name, $birthday, $height, $weight)
        );
        return Player::rowToArray($row);
    }

    /**
     * Deletes the player row with the passed id
     * and returns it on success. Throws an exception
     * on failure.
     * @param db: A valid database connection
     * @param id: The id of the row to delete
     */
    public static function delete($db, $id){
        $row = execute_query($db, 'Player_delete', array(LoggedUser::getId(), $id));
        return Player::rowToArray($row);
    }

    /**
     * Edits a player row based on the passed id.
     * Returns the newly edited row on success. Throws an
     * exception on failure
     * @param db: A valid database connection
     * @param id: The id of the row to edit
     * @param new_id: New id value of the row
     * @param new_birthday: New birthday value of the row
     * @param new_height: New height value of the row
     * @param new_weight: New weight value of the row
     */
    public static function edit($db, $id, $new_id, $new_name, $new_birthday, $new_height, $new_weight){
        $row = execute_query(
            $db, 
            'Player_edit', 
            array(
                LoggedUser::getId(), 
                $id, 
                $new_id, 
                $new_name, 
                $new_birthday, 
                $new_height, 
                $new_weight
            )
        );
        return Player::rowToArray($row);
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
            "birthday" => $row["birthday"],
            "height" => $row["height"],
            "weight" => $row["weight"]
        );
    }
}