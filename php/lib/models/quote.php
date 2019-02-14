<?php
require_once(LIB . '/database.php');
require_once('dbexception.php');
require_once('config.php');


class Quote{
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
                'Quote_find',
                'SELECT 
                    match, 
                    bet_provider, 
                    home_quote, 
                    draw_quote, 
                    away_quote, 
                    created_by 
                FROM quote 
                WHERE match = $1 AND bet_provider = $2;'
            );
            pg_prepare(
                $db,
                'Quote_insert',
                'INSERT INTO quote
                    (match, bet_provider, home_quote, draw_quote, away_quote, created_by) 
                VALUES 
                    ($1, $2, $3, $4, $5, $6) 
                RETURNING 
                    match, bet_provider, home_quote, draw_quote, away_quote, created_by;'
            );
            $prepared = true;
        }
    }

    /**
     * Returns a quote found by its' match & bet_provider.
     * Returns NULL if not found, raises an
     * exception if an error occurs
     */
    public static function find($db, $match, $bet_provider){
        $result = @pg_execute($db, 'Quote_find', array($match, $bet_provider));
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
     * Inserts a quote in the database.
     * Returns the inserted league if success, NULL if the database does
     * not support the RETURNING construct and can't return after an INSERT.
     * Raises an exception if an error occurs.
     */
    public static function insert($db, $match, $bet_provider, $home_quote, $draw_quote, $away_quote, $created_by){
        $result = @pg_execute(
            $db, 
            'Quote_insert', 
            array($match, $bet_provider, $home_quote, $draw_quote, $away_quote, $created_by)
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