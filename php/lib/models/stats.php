<?php
require_once('config.php');
require_once(LIB . '/database.php');
require_once(LIB . '/models/exceptions/DBException.php');
require_once(LIB . '/models/loggeduser.php');
require_once(LIB . '/models/exceptions/Util.php');


class Stats{
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
                'Stats_find',
                'SELECT 
                    player,
                    attribute_date,
                    overall_rating,
                    potential,
                    preferred_foot,
                    attacking_work_rate,
                    defensive_work_rate,
                    crossing,
                    finishing,
                    heading_accuracy,
                    short_passing volleys,
                    dribbling,
                    curve,
                    free_kick_accuracy,
                    long_passing,
                    ball_control,
                    acceleration,
                    sprint_speed,
                    agility,
                    reactions,
                    balance,
                    shot_power,
                    jumping,
                    stamina,
                    strength,
                    long_shots,
                    aggression,
                    interceptions,
                    positioning, 
                    vision,
                    penalties,
                    marking,
                    standing_tackle,
                    sliding_tackle,
                    gk_diving,
                    gk_handling,
                    gk_kicking,
                    gk_positioning,
                    gk_reflexes 
                FROM stats 
                WHERE player = $1 AND attribute_date = $2;'
            );
            pg_prepare(
                $db, 
                'Stats_insert',
                'SELECT * FROM insert_stats(
                    $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12,
                    $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23,
                    $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34,
                    $35, $36, $37, $38, $39, $40, $41
                );'
            );
            pg_prepare(
                $db,
                'Stats_delete',
                'SELECT * FROM delete_stats($1, $2, $3);'
            );
            $prepared = true;
        }
    }

    /**
     * Returns a stats find by a player's id and a date
     * Returns NULL if not found, raises an exception if 
     * an error occurs
     */
    public static function find($db, $player, $attribute_date){
        $result = @pg_execute($db, 'Stats_find', array($player, $attribute_date));
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
     * Inserts a player's stats in a date in the database.
     * Returns an associative array with the newly inserted
     * stats on success, raises an exception if 
     * an error occurs. Exception that can be thrown are:
     * DuplicateDataException, PermissionDeniedException, 
     * ForeignKeyException, DBException
     */
    public static function insert(
        $db, $player, $attribute_date, $overall_rating,
        $potential, $preferred_foot, $attacking_work_rate,
        $defensive_work_rate, $crossing, $finishing,
        $heading_accuracy, $short_passing, $volleys,
        $dribbling, $curve, $free_kick_accuracy, $long_passing,
        $ball_control, $acceleration, $sprint_speed,
        $agility, $reactions, $balance, $shot_power,
        $jumping, $stamina, $strength, $long_shots,
        $aggression, $interceptions, $positioning,
        $vision, $penalties, $marking, $standing_tackle,
        $sliding_tackle, $gk_diving, $gk_handling,
        $gk_kicking, $gk_positioning, $gk_reflexes
    ){
        $row = execute_query($db, 'Stats_insert', array(
            LoggedUser::getId(),
            $player, $attribute_date, $overall_rating,
            $potential, $preferred_foot, $attacking_work_rate,
            $defensive_work_rate, $crossing, $finishing,
            $heading_accuracy, $short_passing, $volleys,
            $dribbling, $curve, $free_kick_accuracy, $long_passing,
            $ball_control, $acceleration, $sprint_speed,
            $agility, $reactions, $balance, $shot_power,
            $jumping, $stamina, $strength, $long_shots,
            $aggression, $interceptions, $positioning,
            $vision, $penalties, $marking, $standing_tackle,
            $sliding_tackle, $gk_diving, $gk_handling,
            $gk_kicking, $gk_positioning, $gk_reflexes
        ));

        return Stats::rowToArray($row);
    }

    public static function delete($db, $player, $attribute_date){
        $row = execute_query($db, 'Stats_delete', array(LoggedUser::getId(), $player, $attribute_date));
        return $row;
    }

    /**
     * Takes an associative array for a database row
     * and returns another associative array with all
     * the useless parameters filtered
     */
    private static function rowToArray($row){
        return array(
            "player" => $row["player"],
            "attribute_date" => $row["attribute_date"],
            "overall_rating" => $row["overall_rating"],
            "potential" => $row["potential"],
            "preferred_foot" => $row["preferred_foot"],
            "attacking_work_rate" => $row["attacking_work_rate"],
            "defensive_work_rate" => $row["defensive_work_rate"],
            "crossing" => $row["crossing"],
            "finishing" => $row["finishing"],
            "heading_accuracy" => $row["heading_accuracy"],
            "short_passing" => $row["short_passing"],
            "volleys" => $row["volleys"],
            "dribbling" => $row["dribbling"],
            "curve" => $row["curve"],
            "free_kick_accuracy" => $row["free_kick_accuracy"],
            "long_passing" => $row["long_passing"],
            "ball_control" => $row["ball_control"],
            "acceleration" => $row["acceleration"],
            "sprint_speed" => $row["sprint_speed"],
            "agility" => $row["agility"],
            "reactions" => $row["reactions"],
            "balance" => $row["balance"],
            "shot_power" => $row["shot_power"],
            "jumping" => $row["jumping"],
            "stamina" => $row["stamina"],
            "strength" => $row["strength"],
            "long_shots" => $row["long_shots"],
            "aggression" => $row["aggression"],
            "interceptions" => $row["interceptions"],
            "positioning" => $row["positioning"], 
            "vision" => $row["vision"],
            "penalties" => $row["penalties"],
            "marking" => $row["marking"],
            "standing_tackle" => $row["standing_tackle"],
            "sliding_tackle" => $row["sliding_tackle"],
            "gk_diving" => $row["gk_diving"],
            "gk_handling" => $row["gk_handling"],
            "gk_kicking" => $row["gk_kicking"],
            "gk_positioning" => $row["gk_positioning"],
            "gk_reflexes" => $row["gk_reflexes"] 
        );
    }
}