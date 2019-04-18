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
                'SELECT * FROM insert_quote($1, $2, $3, $4, $5, $6);'
            );
            pg_prepare(
                $db,
                'Quote_delete',
                'SELECT * FROM delete_quote($1, $2, $3);'
            );
            pg_prepare(
                $db,
                'Quote_edit',
                'SELECT * FROM edit_quote($1, $2, $3, $4, $5, $6);'
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
    public static function insert($db, $match, $bet_provider, $home_quote, $draw_quote, $away_quote){
        $row = execute_query(
            $db, 
            'Quote_insert', 
            array(LoggedUser::getId(), $match, $bet_provider, $home_quote, $draw_quote, $away_quote)
        );

        return Quote::rowToArray($row);
    }

    /**
     * Deletes the quote row with the passed id
     * and returns it on success. Throws an exception
     * on failure.
     */
    public static function delete($db, $match, $bet_provider){
        $row = execute_query(
            $db, 
            'Quote_delete', 
            array(
                LoggedUser::getId(), 
                $match, 
                $bet_provider
            )
        );
        return Quote::rowToArray($row);
    }

    /**
     * Edits a quote row based on the passed match & bet provider.
     * Returns the newly edited row on success. Throws an
     * exception on failure
     * @param db: A valid database connection
     * @param match: The match the quote refers to
     * @param bet_provider: ID of the bet provider the quote is for
     * @param new_home_quote: New quote for the home team value of the row
     * @param new_draw_quote: New quote for a draw in the match value of the row
     * @param new_away_quote: New quote for the away team value of the row
     */
    public static function edit(
        $db, 
        $match, 
        $bet_provider, 
        $new_home_quote, 
        $new_draw_quote, 
        $new_away_quote
    )
    {
        $row = execute_query(
            $db, 
            'Quote_edit', 
            array(
                LoggedUser::getId(), 
                $match, 
                $bet_provider, 
                $new_home_quote, 
                $new_draw_quote, 
                $new_away_quote
            )
        );
        return Quote::rowToArray($row);
    }
    /**
     * Takes an associative array for a database row
     * and returns another associative array with all
     * the useless parameters filtered
     */
    private static function rowToArray($row){
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