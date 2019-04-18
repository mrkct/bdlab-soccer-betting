<?php
require_once(LIB . '/models/bet_provider.php');
require_once(LIB . '/models/quote.php');
require_once(LIB . '/models/loggeduser.php');
require_once(LIB . '/models/exceptions/DuplicateDataException.php');
require_once(LIB . '/models/exceptions/DBException.php');


/**
 * Reads all the betting providers id's from the file header
 * and returns an array of them. Their position is the same
 * as the order they appear in the header.
 */
function read_providers($heading){
    $providers = array();
    for($i = 1; $i < sizeof($heading); $i += 3){
        $id = substr($heading[$i], 0, strlen($heading[$i])-1);
        array_push($providers, $id);
    }

    return $providers;
}

/**
 * Converts a CSV row array into an associative array.
 * $row: The CSV row array
 * $providers: An array of all the bet providers in the
 * order they appear in the CSV file header.
 */
function bet_read_row($row, $providers){
    $match = array(
        "id" => $row[0],
        "quotes" => array()
    );
    for($i = 1; $i < sizeof($row); $i += 3){
        if( !empty($row[$i]) ){
            $provider = $providers[($i-1) / 3];
            $match["quotes"][$provider] = array(
                "home" => $row[$i], 
                "draw" => $row[$i+1], 
                "away" => $row[$i+2]
            );
        }
    }

    return $match;
}

/**
 * Inserts quotes for a single bet provider for a single match
 * in the db.
 * $db: A database connection
 * $matchquotes: An associative array in the format specified by
 * the function 'bet_read_row' above
 */
function bet_insert($db, $matchquotes){
    Quote::prepare($db);
    foreach(array_keys($matchquotes["quotes"]) as $provider){
        try{
            Quote::insert(
                $db, 
                $matchquotes["id"], 
                $provider, 
                $matchquotes["quotes"][$provider]["home"],
                $matchquotes["quotes"][$provider]["draw"],
                $matchquotes["quotes"][$provider]["away"]
            );
        }catch(DuplicateDataException $e){}
    }
}

/**
 * Imports a csv with the bet.csv format.
 * $file: A file handler, for example the result of fopen
 * $db: A connection handler to the database
 */
function bet_import($file, $db){
    $counted_rows = 0;
    $error_rows = 0;
    $error_log = array();
    
    // Reads all the betting providers and adds them to the db
    $heading = fgetcsv($file, 0, ",");
    $providers = read_providers($heading);
    BetProvider::prepare($db);
    foreach($providers as $provider){
        try{
            BetProvider::insert($db, $provider, NULL);
        }catch(DuplicateDataException $e){}
    }
    
    while($row = fgetcsv($file, 0, ",")){
        $match = bet_read_row($row, $providers);
        try{
            $counted_rows++;
            bet_insert($db, $match);
        }catch(DBException $e){
            /**
             * Note: We do not catch the singular exceptions because they
             * all get treated the same way
             */
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