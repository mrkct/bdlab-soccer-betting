<?php

require_once(LIB . '/models/team.php');
require_once(LIB . '/models/player.php');
require_once(LIB . '/models/match.php');
require_once(LIB . '/models/country.php');
require_once(LIB . '/models/league.php');
require_once(LIB . '/csv_import/InvalidDataException.php');

/**
 * Given an array representing a match.csv row returns
 * a map with easier to access data and all invalid data
 * set to NULL
 */
function match_filter_row($row){
    $filtered = array(
        "id" => $row[0],
        "country" => $row[1],
        "league_name" => $row[2],
        "season" => $row[3],
        "stage" => $row[4],
        "played_on" => $row[5],
        "home_team" => array(
            "id" => $row[6],
            "long_name" => $row[7],
            "short_name" => $row[8],
            "goals" => $row[12],
            "players" => array()
        ),
        "away_team" => array(
            "id" => $row[9],
            "long_name" => $row[10],
            "short_name" => $row[11],
            "goals" => $row[13],
            "players" => array()
        )
    );

    // Index from which the players info starts
    // OFFSET_HPLAYERS => Offset for home team players
    // OFFSET_APLAYERS => Offset for away team players
    $PLAYER_ATTRIBUTES = 5;
    $OFFSET_HPLAYERS = 14;
    $OFFSET_APLAYERS = $OFFSET_HPLAYERS + (11 * $PLAYER_ATTRIBUTES);
    
    for($i = 0; $i < 11; $i++){
        $hteam = $OFFSET_HPLAYERS + $PLAYER_ATTRIBUTES * $i;
        $ateam = $OFFSET_APLAYERS + $PLAYER_ATTRIBUTES * $i;
        array_push($filtered["home_team"]["players"], array(
            "id"        => !empty($row[$hteam])? $row[$hteam] : NULL,
            "name"      => !empty($row[$hteam + 1])? $row[$hteam + 1] : NULL,
            "birthday"  => !empty($row[$hteam + 2])? $row[$hteam + 2] : NULL,
            "height"    => !empty($row[$hteam + 3])? $row[$hteam + 3] : NULL,
            "weight"    => !empty($row[$hteam + 4])? $row[$hteam + 4] : NULL
        ));
        array_push($filtered["away_team"]["players"], array(
            "id"        => !empty($row[$ateam])? $row[$ateam] : NULL,
            "name"      => !empty($row[$ateam + 1])? $row[$ateam + 1] : NULL,
            "birthday"  => !empty($row[$ateam + 2])? $row[$ateam + 2] : NULL,
            "height"    => !empty($row[$ateam + 3])? $row[$ateam + 3] : NULL,
            "weight"    => !empty($row[$ateam + 4])? $row[$ateam + 4] : NULL
        ));
    }

    return $filtered;
}

/**
 * Given a filtered row inserts all related data in the db
 */
function match_insert_row($db, $row){
    Team::prepare($db);
    $hometeam = Team::find($db, $row["home_team"]["id"]);
    if( $hometeam == NULL ){
        $hometeam = Team::insert(
            $db, 
            $row["home_team"]["id"],
            $row["home_team"]["short_name"],
            $row["home_team"]["long_name"]
        );
    }
    $awayteam = Team::find($db, $row["away_team"]["id"]);
    if( $awayteam == NULL ){
        $awayteam = Team::insert(
            $db, 
            $row["away_team"]["id"],
            $row["away_team"]["short_name"],
            $row["away_team"]["long_name"]
        );
    }
    
    Country::prepare($db);
    League::prepare($db);
    $country = Country::findByName($db, $row["country"]);
    if( $country == NULL ){
        throw new InvalidDataException(
            'Unknown country "' . $row["country"] . '". in match id ' . $row["id"]
        );
    }
    $league = League::findByNameAndCountry($db, $row["league_name"], $country["iso3"]);
    if( $league == NULL ){
        $league = League::insert($db, $row["league_name"], $country["iso3"]);
    }
    
    Player::prepare($db);
    for($i = 0; $i < sizeof($row["home_team"]["players"]); $i++){
        if( $row["home_team"]["players"][$i]["id"] != NULL ){
            $player = $row["home_team"]["players"][$i];
            if( Player::find($db, $player["id"]) == NULL ){
                Player::insert(
                    $db, 
                    $player["id"], 
                    $player["name"], 
                    $player["birthday"], 
                    $player["height"], 
                    $player["weight"]
                );
            }
        }
        if( $row["away_team"]["players"][$i]["id"] != NULL ){
            $player = $row["away_team"]["players"][$i];
            if( Player::find($db, $player["id"]) == NULL ){
                Player::insert(
                    $db, 
                    $player["id"], 
                    $player["name"], 
                    $player["birthday"], 
                    $player["height"], 
                    $player["weight"]
                );
            }
        }
    }

    Match::prepare($db);
    if( Match::find($db, $row["id"]) == NULL ){
        Match::insert(
            $db, 
            $row["id"], 
            $league["id"], 
            $row["season"], 
            $row["stage"], 
            $row["played_on"], 
            $hometeam["id"], 
            $awayteam["id"], 
            $row["home_team"]["goals"], 
            $row["away_team"]["goals"], 
            $_SESSION["id"]
        );
    }

    for($i = 0; $i < sizeof($row["home_team"]["players"]); $i++){
        $player = $row["home_team"]["players"][$i];
        if( $player["id"] != NULL ){
            if( !Match::playedExists($db, $player["id"], $row["id"], $hometeam["id"]) ){
                Match::insertPlayed($db, $player["id"], $row["id"], $hometeam["id"]);
            }
        }
        $player = $row["away_team"]["players"][$i];
        if( $player["id"] != NULL ){
            if( !Match::playedExists($db, $player["id"], $row["id"], $awayteam["id"]) ){
                Match::insertPlayed($db, $player["id"], $row["id"], $awayteam["id"]);
            }
        }
    }
    return true;
}

/**
 * Imports a csv with the match.csv format.
 * $file: A file handler, for example the result of fopen
 * $db: A connection handler to the database
 */
function match_import_csv($file, $db){
    $counted_rows = 0;
    $error_rows = 0;
    $error_log = array();
    
    fgetcsv($file, 0, ","); // To skip the heading line
    while($row = fgetcsv($file, 0, ",")){
        $row = match_filter_row($row);
        try{
            $counted_rows++;
            match_insert_row($db, $row);
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