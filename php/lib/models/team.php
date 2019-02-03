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
                'find_team',
                'SELECT id, shortname, longname FROM team WHERE id = $1'
            );
            pg_prepare(
                $db, 
                'insert_team',
                'INSERT INTO team(id, shortname, longname) VALUES ($1, $2, $3)'
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
        $result = @pg_execute($db, 'find_team', array($id));
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
     * Returns true if success, raises an exception if
     * an error occurs.
     */
    public static function insert($db, $id, $shortname, $longname){
        $result = @pg_execute($db, 'insert_team', array($id, $shortname, $longname));
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }

        return true;
    }
}