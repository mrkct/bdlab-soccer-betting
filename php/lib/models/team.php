<?php
require_once(LIB . '/database.php');
require_once('dbexception.php');
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
                'INSERT INTO team(id, shortname, longname) VALUES ($1, $2, $3) RETURNING id, shortname, longname;'
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
     * Returns the inserted team if success, NULL if the database does
     * not support the RETURNING construct and can't return after an INSERT.
     * Raises an exception if an error occurs.
     */
    public static function insert($db, $id, $shortname, $longname){
        $result = @pg_execute($db, 'Team_insert', array($id, $shortname, $longname));
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