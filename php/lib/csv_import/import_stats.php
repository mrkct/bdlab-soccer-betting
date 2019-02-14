<?php

require(LIB . '/models/stats.php');
require_once(LIB . '/csv_import/InvalidDataException.php');

/**
 * Takes a value and an array of accepted values.
 * If the value is in the array it returns itself,
 * otherwise it returns NULL
 */
function valid_values($value, $accepted){
    if( in_array($value, $accepted) ){
        return $value;
    }

    return NULL;
}

/**
 * Converts a CSV row array into an associative array.
 * $row: The CSV row array
 */
function stats_read_row($row){
    $stats = array(
        "player"                => empty($row[0])? NULL : $row[0],
        "attribute_date"        => empty($row[1])? NULL : $row[1],
        "overall_rating"        => empty($row[2])? NULL : $row[2],
        "potential"             => empty($row[3])? NULL : $row[3],
        "preferred_foot"        => empty($row[4])? NULL : $row[4],
        "attacking_work_rate"   => empty($row[5])? NULL : $row[5],
        "defensive_work_rate"   => empty($row[6])? NULL : $row[6],
        "crossing"              => empty($row[7])? NULL : $row[7],
        "finishing"             => empty($row[8])? NULL : $row[8],
        "heading_accuracy"      => empty($row[9])? NULL : $row[9],
        "short_passing"         => empty($row[10])? NULL : $row[10],
        "volleys"               => empty($row[11])? NULL : $row[11],
        "dribbling"             => empty($row[12])? NULL : $row[12],
        "curve"                 => empty($row[13])? NULL : $row[13],
        "free_kick_accuracy"    => empty($row[14])? NULL : $row[14],
        "long_passing"          => empty($row[15])? NULL : $row[15],
        "ball_control"          => empty($row[16])? NULL : $row[16],
        "acceleration"          => empty($row[17])? NULL : $row[17],
        "sprint_speed"          => empty($row[18])? NULL : $row[18],
        "agility"               => empty($row[19])? NULL : $row[19],
        "reactions"             => empty($row[20])? NULL : $row[20],
        "balance"               => empty($row[21])? NULL : $row[21],
        "shot_power"            => empty($row[22])? NULL : $row[22],
        "jumping"               => empty($row[23])? NULL : $row[23],
        "stamina"               => empty($row[24])? NULL : $row[24],
        "strength"              => empty($row[25])? NULL : $row[25],
        "long_shots"            => empty($row[26])? NULL : $row[26],
        "aggression"            => empty($row[27])? NULL : $row[27],
        "interceptions"         => empty($row[28])? NULL : $row[28],
        "positioning"           => empty($row[29])? NULL : $row[29],
        "vision"                => empty($row[30])? NULL : $row[30],
        "penalties"             => empty($row[31])? NULL : $row[31],
        "marking"               => empty($row[32])? NULL : $row[32],
        "standing_tackle"       => empty($row[33])? NULL : $row[33],
        "sliding_tackle"        => empty($row[34])? NULL : $row[34],
        "gk_diving"             => empty($row[35])? NULL : $row[35],
        "gk_handling"           => empty($row[36])? NULL : $row[36],
        "gk_kicking"            => empty($row[37])? NULL : $row[37],
        "gk_positioning"        => empty($row[38])? NULL : $row[38],
        "gk_reflexes"           => empty($row[39])? NULL : $row[39]
    );

    // Used to shorten code afterwards.
    // lmh stands for 'low medium high'
    $lmh = array('low', 'medium', 'high');

    $stats['attacking_work_rate'] = valid_values($stats['attacking_work_rate'], $lmh);
    $stats['defensive_work_rate'] = valid_values($stats['defensive_work_rate'], $lmh);
    $stats['preferred_foot'] = valid_values($stats['preferred_foot'], array('left', 'right'));

    return $stats;
}

/**
 * Inserts stats for a single player in a single date
 * in the db.
 * $db: A database connection
 * $stats: An associative array in the format specified by
 * the function 'stats_read_row' above
 */
function stats_insert($db, $stats){
    Stats::prepare($db);
    if( Stats::find($db, $stats['player'], $stats['attribute_date']) == NULL ){
        Stats::insert(
            $db, 
            $stats['player'], 
            $stats['attribute_date'], 
            $stats['overall_rating'], 
            $stats['potential'],
            $stats['preferred_foot'],
            $stats['attacking_work_rate'],
            $stats['defensive_work_rate'],
            $stats['crossing'],
            $stats['finishing'],
            $stats['heading_accuracy'],
            $stats['short_passing'],
            $stats['volleys'],
            $stats['dribbling'],
            $stats['curve'],
            $stats['free_kick_accuracy'],
            $stats['long_passing'],
            $stats['ball_control'],
            $stats['acceleration'],
            $stats['sprint_speed'],
            $stats['agility'],
            $stats['reactions'],
            $stats['balance'],
            $stats['shot_power'],
            $stats['jumping'],
            $stats['stamina'],
            $stats['strength'],
            $stats['long_shots'],
            $stats['aggression'],
            $stats['interceptions'],
            $stats['positioning'],
            $stats['vision'],
            $stats['penalties'],
            $stats['marking'],
            $stats['standing_tackle'],
            $stats['sliding_tackle'],
            $stats['gk_diving'],
            $stats['gk_handling'],
            $stats['gk_kicking'],
            $stats['gk_positioning'],
            $stats['gk_reflexes']
        );
    }

    return true;
}

/**
 * Imports a csv with the player_attribute.csv format.
 * $file: A file handler, for example the result of fopen
 * $db: A connection handler to the database
 */
function stats_import($file, $db){
    $counted_rows = 0;
    $error_rows = 0;
    $error_log = array();
    
    fgetcsv($file, 0, ","); // To skip the heading line
    while($row = fgetcsv($file, 0, ",")){
        $stats = stats_read_row($row);
        try{
            $counted_rows++;
            stats_insert($db, $stats);
        }catch(InvalidDataException $e){
            $error_rows++;
            array_push($error_log, array(
                "line" => $counted_rows,
                "message" => $e->getMessage()
            ));
        }catch(DBException $e){
            $error_rows++;
            array_push($error_log, array(
                "line" => $counted_rows,
                "message" => $e->getMessage()
            ));
        }
    }

    return array(
        "total_rows" => $counted_rows,
        "error_rows" => $error_rows,
        "error_log" => $error_log
    );
}