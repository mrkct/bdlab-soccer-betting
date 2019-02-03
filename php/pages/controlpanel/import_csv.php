<?php
    require_once('config.php');
    require_once(COMPONENTS . '/logincheck.php');
    if( !$logged ){
        header('location: /bdlab/php/login.php');
        exit();
    }

    define('TYPE_MATCH', 'match');
    define('TYPE_BET', 'bet');
    define('TYPE_STATS', 'player_attribute');

    class InvalidDataException extends Exception {}

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

    $error_log = array();
    $counted_rows = 0;
    $error_rows = 0;
    if( isset($_FILES["file"]) ){
        require_once(LIB . '/database.php');
        require_once(LIB . '/models/team.php');
        require_once(LIB . '/models/player.php');
        require_once(LIB . '/models/match.php');
        require_once(LIB . '/models/country.php');
        require_once(LIB . '/models/league.php');

        $db = db_connect();
        $file = fopen($_FILES["file"]["tmp_name"], "r");

        if( !$file ){
            $error = true;
            $errormsg = "Failed to open file on server";
        } else {
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
            
        }
    }
?>
<!DOCTYPE html>
<html class="has-background-light full-height">
    <head>
        <title>Soccer Bets - Import CSV</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
    </head>
    <body class="has-background-light">
        <?php include_once(COMPONENTS . '/navbar.php'); ?>
        <div class="container">
            <div class="columns">
                <?php require_once(COMPONENTS . '/controlpanel-menu.php'); ?>
                <div class="container column is-three-quarters">
                    <h2 class="title is-2">Import data from CSV</h2>
                    <form class="form controlpanel-form" method="POST" enctype="multipart/form-data">
                        <input type="file" name="file" class="file" />
                        <label class="radio">
                            <input type="radio" name="type" value="<?php echo TYPE_MATCH; ?>" />
                            Match Data (match.csv)
                        </label>
                        <br>
                        <label class="radio">
                            <input type="radio" name="type" value="<?php echo TYPE_BET; ?>" />
                            Bet Data (bet.csv)
                        </label>
                        <br>
                        <label class="radio">
                            <input type="radio" name="type" value="<?php echo TYPE_STATS; ?>" />
                            Player's Stats (player.csv)
                        </label>
                        <input class="input button is-link" type="submit" value="Import data" />
                    </form>
                    <?php
                        if( $counted_rows > 0 && $error_rows == 0 ):
                            ?>
                        <div class="notification is-success">
                            <?php echo $counted_rows; ?> added successfully.
                        </div>
                        <?php endif; ?>
                    <?php
                        if( $error_rows != 0 ):
                            ?>
                            <div class="notification is-danger">
                                <?php echo $error_rows; ?> out of <?php echo $counted_rows; ?> could not be added because of errors.
                                <button onClick="toggleErrorPanel()" class="button is-link">
                                    <span class="icon is-small">
                                        <i class="fas fa-chevron-circle-down"></i>
                                    </span>
                                    <span>Show more details</span>
                                </button>
                                <pre id="errorPanel" style="display: none"><?php
                                        foreach($error_log as $error){
                                            echo "At line ", $error["line"], ":", $error["message"], "\n";
                                        }
                                    
                              ?></pre>
                            </div>
                        <?php endif; ?>
                </div>
                <script type="text/javascript">
                    function toggleErrorPanel(){
                        var errorPanel = document.getElementById("errorPanel");
                        if( errorPanel.style.display == "block" ){
                            errorPanel.style.display = "none";
                        } else {
                            errorPanel.style.display = "block";
                        }
                    }
                </script>
            </div>
        </div>
    </body>
</html>