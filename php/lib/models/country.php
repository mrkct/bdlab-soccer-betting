<?php
require_once(LIB . '/database.php');
require_once('dbexception.php');
require_once('config.php');


class Country{
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
                'Country_findByISO3',
                'SELECT iso3, name FROM country WHERE iso3 = $1;'
            );
            pg_prepare(
                $db,
                'Country_findByName',
                'SELECT iso3, name FROM country WHERE name = $1;'
            );
            $prepared = true;
        }
    }

    /**
     * Returns a country found by its' iso3 code.
     * Returns NULL if not found, raises an exception 
     * if an error occurs
     */
    public static function findByISO3($db, $iso3){
        $result = @pg_execute($db, 'Country_findByISO3', array($iso3));
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
     * Returns a country found by its' name.
     * Returns NULL if not found, raises an exception 
     * if an error occurs
     */
    public static function findByName($db, $name){
        $result = @pg_execute($db, 'find_country_name', array($name));
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