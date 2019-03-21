<?php
require_once('config.php');
require_once(LIB . '/database.php');
require_once(LIB . '/models/exceptions/DBException.php');
require_once(LIB . '/models/loggeduser.php');
require_once(LIB . '/models/exceptions/Util.php');


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
                'SELECT 
                    match, bet_provider, home_quote, draw_quote, away_quote, created_by, 
                    success, error_code, message 
                 FROM insert_quote($1, $2, $3, $4, $5, $6);'
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
            array(LoggedUser::getId(), $match, $bet_provider, $home_quote, $draw_quote, $away_quote)
        );
        if( !$result ){
            throw new DBException(pg_last_error($db));
        } 
        $row = pg_fetch_assoc($result);
        result_row_to_exception($row);

        return array(
            "match" => $row["match"], 
            "bet_provider" => $row["bet_provider"], 
            "home_quote" => $row["home_quote"], 
            "draw_quote" => $row["draw_quote"], 
            "away_quote" => $row["away_quote"], 
            "created_by" => $row["created_by"] 
        );
    }
}