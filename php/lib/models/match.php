<?php
require_once('config.php');
require_once(LIB . '/database.php');
require_once('dbexception.php');


class Match{

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
                'Match_find',
                'SELECT 
                    id, 
                    league, 
                    season, 
                    stage, 
                    played_on, 
                    hometeam_goals, 
                    awayteam_goals, 
                    hometeam, 
                    awayteam, 
                    created_by 
                FROM match 
                WHERE id = $1'
            );
            pg_prepare(
                $db, 
                'Match_insert',
                'INSERT INTO match(
                    id, 
                    league, 
                    season, 
                    stage, 
                    played_on, 
                    hometeam_goals, 
                    awayteam_goals, 
                    hometeam, 
                    awayteam, 
                    created_by
                ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10) 
                RETURNING 
                    id, 
                    league, 
                    season, 
                    stage, 
                    played_on, 
                    hometeam_goals, 
                    awayteam_goals, 
                    hometeam, 
                    awayteam, 
                    created_by;'
            );
            pg_prepare(
                $db,
                'Match_insertPlayed',
                'INSERT INTO played(player, match, team) VALUES ($1, $2, $3);'
            );
            pg_prepare(
                $db,
                'Match_playedExists',
                'SELECT player, match, team FROM played WHERE player = $1 AND match = $2 AND team = $3;'
            );
            $prepared = true;
        }
    }

    /**
     * Returns a match found by its' id.
     * Returns NULL if not found, raises an
     * exception if an error occurs
     */
    public static function find($db, $id){
        $result = @pg_execute($db, 'Match_find', array($id));
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
     * Inserts a match in the database.
     * Returns the inserted match if success, NULL if the database does
     * not support the RETURNING construct and can't return after an INSERT.
     * Raises an exception if an error occurs.
     */
    public static function insert($db, $id, $league, $season, $stage, $played_on, $hometeam,
                                  $awayteam, $hometeam_goals, $awayteam_goals, $created_by){
        $result = @pg_execute($db, 'Match_insert', array(
            $id,
            $league,
            $season,
            $stage,
            $played_on,
            $hometeam_goals,
            $awayteam_goals,
            $hometeam,
            $awayteam,
            $created_by
        ));
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
     * Records that a player played with a team in a specific match.
     * Returns true if success, raises an exception if an error occurs.
     */
    public static function insertPlayed($db, $player, $match, $team){
        $result = @pg_execute($db, 'Match_insertPlayed', array(
            $player,
            $match,
            $team
        ));
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }

        return true;
    }

    /**
     * Returns if a row with the passed arguments exists in the 'played' table.
     * Returns true if a row exists, false otherwise. Raises an exception
     * if an error occurs.
     */
    public static function playedExists($db, $player, $match, $team){
        $result = @pg_execute($db, 'Match_playedExists', array(
            $player,
            $match,
            $team
        ));
        if( !$result ){
            throw new DBException(pg_last_error($db));
        }

        if( pg_fetch_assoc($result) ){
            return true;
        } else {
            return false;
        }
    }
}