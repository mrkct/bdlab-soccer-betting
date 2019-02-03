<?php
require_once(LIB . '/database.php');
require_once('dbexception.php');
require_once('config.php');


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
                'INSERT INTO league(name, country) VALUES ($1, $2) RETURNING id, name, country;'
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
     * Returns the inserted league if success, NULL if the database does
     * not support the RETURNING construct and can't return after an INSERT.
     * Raises an exception if an error occurs.
     */
    public static function insert($db, $name, $country){
        $result = @pg_execute(
            $db, 
            'League_insert', 
            array($name, $country)
        );
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