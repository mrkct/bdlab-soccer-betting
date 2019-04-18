<?php
require_once('config.php');
require_once(LIB . '/database.php');
require_once(LIB . '/models/exceptions/DBException.php');
require_once(LIB . '/models/exceptions/DuplicateDataException.php');
require_once(LIB . '/models/exceptions/PermissionDeniedException.php');
require_once(LIB . '/models/loggeduser.php');
require_once(LIB . '/models/exceptions/Util.php');


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
                'SELECT 
                    id, league, season, stage, played_on, hometeam_goals, 
                    awayteam_goals, hometeam, awayteam, created_by, 
                    success, error_code, message
                FROM insert_match(
                    $1, $2, $3, $4, $5, 
                    $6, $7, $8, $9, $10
                );'
            );
            pg_prepare(
                $db,
                'Match_delete',
                'SELECT * FROM delete_match($1, $2);'
            );
            pg_prepare(
                $db,
                'Match_edit',
                'SELECT * FROM edit_match($1, $2, $3, $4, $5, $6, $7, $8, $9, $10);'
            );
            pg_prepare(
                $db,
                'Match_insertPlayed',
                'SELECT * FROM insert_played($1, $2, $3, $4);'
            );
            pg_prepare(
                $db,
                'Match_playedExists',
                'SELECT player, match, team FROM played WHERE player = $1 AND match = $2 AND team = $3;'
            );
            pg_prepare(
                $db,
                'Match_deletePlayed',
                'SELECT * FROM delete_played($1, $2, $3);'
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
     * Returns an associative array with the newly inserted
     * match on success, raises an exception if 
     * an error occurs. Exception that can be thrown are:
     * DuplicateDataException, PermissionDeniedException, 
     * ForeignKeyException, DBException
     */
    public static function insert($db, $id, $league, $season, $stage, $played_on, $hometeam,
                                  $awayteam, $hometeam_goals, $awayteam_goals, $created_by){
        $row = execute_query($db, 'Match_insert', array(
            LoggedUser::getId(),
            $id,
            $league,
            $season,
            $stage,
            $played_on,
            $hometeam,
            $awayteam,
            $hometeam_goals,
            $awayteam_goals
        ));
        return Match::matchRowToArray($row);
    }

    /**
     * Deletes the match row with the passed id
     * and returns it on success. Throws an exception
     * on failure.
     */
    public static function delete($db, $id){
        $row = execute_query($db, 'Match_delete', array(LoggedUser::getId(), $id));
        return Match::matchRowToArray($row);
    }

    /**
     * Edits a match row based on the passed id.
     * Returns the newly edited row on success. Throws an
     * exception on failure
     * @param db: A valid database connection
     * @param id: Id of the match the new data refers to
     * @param new_league: New league the match belongs to
     * @param new_season: Updated season value
     * @param new_stage: Updated stage value
     * @param new_played_on: Updated date on when the match was played
     * @param new_hometeam: Updated hometeam ID
     * @param new_awayteam: Updated awayteam ID
     * @param new_hometeam_goals: Updated goals for hometeam
     * @param new_awayteam_goals: Updated goals for awayteam
     */
    public static function edit(
            $db, 
            $id,
            $new_league, 
            $new_season, 
            $new_stage, 
            $new_played_on, 
            $new_hometeam, 
            $new_awayteam, 
            $new_hometeam_goals, 
            $new_awayteam_goals
        ){
        
        $row = execute_query(
            $db, 
            'Match_edit', 
            array(
                LoggedUser::getId(), 
                $id,
                $new_league, 
                $new_season, 
                $new_stage, 
                $new_played_on, 
                $new_hometeam_goals, 
                $new_awayteam_goals,
                $new_hometeam, 
                $new_awayteam
            )
        );
    }

    /**
     * Records that a player played with a team in a specific match.
     * Returns the inserted row on success, raises an exception on
     * failure. Exceptions that can be thrown are:
     * PermissionDeniedException, ForeignKeyException, DuplicateDataException,
     * DBException
     */
    public static function insertPlayed($db, $player, $match, $team){
        $row = execute_query($db, 'Match_insertPlayed', array(
            LoggedUser::getId(),
            $player,
            $match,
            $team
        ));

        return Match::playedRowToArray($row);
    }

    /**
     * Returns if a row with the passed arguments exists in the 'played' table.
     * Returns true if a row exists, false otherwise. Raises an exception
     * if an error occurs.
     */
    public static function playedExists($db, $player, $match, $team){
        $row = execute_query($db, 'Match_playedExists', array($player, $match, $team)); 
        return $row != NULL;
    }

    /**
     * Deletes the played row with the passed id
     * and returns it on success. Throws an exception
     * on failure.
     */
    public static function deletePlayed($db, $player, $match){
        $row = execute_query($db, 'Match_deletePlayed', array(LoggedUser::getId(), $player, $match));
        return Match::playedRowToArray($row);
    }

    /**
     * Takes an associative array for a database row
     * and returns another associative array with all
     * the useless parameters filtered
     */
    private static function matchRowToArray($row){
        return array(
            "id" => $row["id"],
            "league" => $row["league"],
            "season" => $row["season"],
            "stage" => $row["stage"],
            "played_on" => $row["played_on"],
            "hometeam" => $row["hometeam"],
            "awayteam" => $row["awayteam"],
            "hometeam_goals" => $row["hometeam_goals"],
            "awayteam_goals" => $row["awayteam_goals"]
        );
    }

    /**
     * Takes an associative array for a database row
     * and returns another associative array with all
     * the useless parameters filtered
     */
    private static function playedRowToArray($row){
        return array(
            "player" => $row["player"],
            "match" => $row["match"],
            "team" => $row["team"]
        );
    }
}