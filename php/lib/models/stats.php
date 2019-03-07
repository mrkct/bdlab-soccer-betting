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
                'SELECT success, error_code, message FROM insert_stats(
                    $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12,
                    $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23,
                    $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34,
                    $35, $36, $37, $38, $39, $40, $41
                );'
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
     * Inserts a stats row in the database. $stats is an array
     * Returns the inserted stats row if successfull, NULL if the database does
     * not support the RETURNING construct and can't return after an INSERT.
     * Raises an exception if an error occurs.
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
        $result = @pg_execute($db, 'Stats_insert', array(
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

        if( !$result ){
            throw new DBException(pg_last_error($db));
        }
        $row = pg_fetch_assoc($result);
        result_row_to_exception($row);

        return true;
    }
}