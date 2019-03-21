<?php
    require_once('config.php');
    require_once(LIB . '/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/match.php');
    require_once(LIB . '/models/loggeduser.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/paginated-select.php');
    require_once(COMPONENTS . '/error_message.php');
    require_once(COMPONENTS . '/success-message.php');
    
    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }
    if( $_SESSION['role'] == 'partner' ){
        redirect(PAGE_FORBIDDEN);
        exit();
    }

    $db = db_connect();

    define("STATE_SELECT_LEAGUE", "select_league");
    define("STATE_SELECT_HOMETEAM", "select_hometeam");
    define("STATE_SELECT_AWAYTEAM", "select_awayteam");
    define("STATE_COMPLETE", "complete");
    $valid_states = array(
        STATE_SELECT_LEAGUE,
        STATE_SELECT_HOMETEAM,
        STATE_SELECT_AWAYTEAM,
        STATE_COMPLETE
    );

    $state = STATE_SELECT_LEAGUE;
    if( isset($_GET["state"]) ){
        $state = $_GET["state"];
        if( !in_array($state, $valid_states) ){
            $state = STATE_SELECT_LEAGUE;
        }
        
        /**
         * Here we check if for each state the required parameters were passed.
         * If not we reset the state to the first step
         */
        switch($state){
        case STATE_SELECT_LEAGUE:
            // Nothing to check, intentionally left blank for clarity
            break;
        case STATE_SELECT_HOMETEAM:
            if( !isset($_GET["league"]) ){
                $state = STATE_SELECT_LEAGUE;
            }
            break;
        case STATE_SELECT_AWAYTEAM:
            if( !isset($_GET["league"]) || !isset($_GET["hometeam"]) ){
                $state = STATE_SELECT_LEAGUE;
            }
            break;
        case STATE_COMPLETE:
            if( !isset($_GET["league"]) || !isset($_GET["hometeam"]) || !isset($_GET["awayteam"]) ){
                $state = STATE_SELECT_LEAGUE;
            }
            break;
        }
    }

    if( isset($_POST["league"]) ){
        try{
            Match::prepare($db);
            $match = Match::insert(
                $db,
                NULL,
                $_POST["league"],
                $_POST["season"],
                $_POST["stage"],
                $_POST["played_on"],
                $_POST["hometeam"],
                $_POST["awayteam"],
                $_POST["hometeam_goals"],
                $_POST["awayteam_goals"],
                LoggedUser::getId()
            );
            redirect(PAGES . '/controlpanel/add/match_players.php?id=' . $match["id"]);
        }catch(PermissionDeniedException $e){
            $error = "You are not allowed to insert league's data";
        }catch(DuplicateDataException $e){
            $error = "There is already a league with that id";
        }catch(ForeignKeyException $e){
            $error = "There was an error with the selected league and teams. Reload this page and retry";
        }catch(DBException $e){
            $error = "An unknown error occurred[" . $e->getMessage() . "]";
        }
    }
?>
<!DOCTYPE html>
<html class="has-background-light full-height">
    <head>
        <title>Soccer Bets - Control Panel</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
    </head>
    <body class="has-background-light">
        <?php include_once(COMPONENTS . '/navbar.php'); ?>
        <div class="container">
            <div class="columns">
                <?php require_once(COMPONENTS . '/controlpanel-menu.php'); ?>
                <div class="container column is-three-quarters form-container">
                    <h2 class="title is-2 title-centered">
                    <?php
                        switch($state){
                        case STATE_SELECT_LEAGUE:
                            echo "Select the league the match was played in";
                            break;
                        case STATE_SELECT_HOMETEAM:
                            echo "Select the home team";
                            break;
                        case STATE_SELECT_AWAYTEAM:
                            echo "Select the away team";
                            break;
                        case STATE_COMPLETE:
                            echo "Fill in the remaining information";
                            break;
                        }
                    ?>
                    </h2>
                    <?php
                        $league_display = function($item){
                            return $item["name"];
                        };

                        $league_link = function($item){
                            return sprintf("?state=%s&league=%d", STATE_SELECT_HOMETEAM, $item["id"]);
                        };

                        $team_display = function($item){
                            return $item["longname"];
                        };

                        $hometeam_link = function($item){
                            return sprintf(
                                "?state=%s&league=%d&hometeam=%d", 
                                STATE_SELECT_AWAYTEAM, 
                                $_GET["league"],
                                $item["id"]
                            );
                        };

                        $awayteam_link = function($item){
                            return sprintf(
                                "?state=%s&league=%d&hometeam=%d&awayteam=%d", 
                                STATE_COMPLETE, 
                                $_GET["league"],
                                $_GET["hometeam"],
                                $item["id"]
                            );
                        };

                        switch($state){
                        case STATE_SELECT_LEAGUE:
                            $total_leagues = pg_fetch_assoc(
                                pg_query($db, "SELECT COUNT(*) AS leagues FROM league;")
                            )["leagues"];
                            create_paginated_select_form(
                                "SELECT * FROM league ORDER BY name LIMIT $1 OFFSET $2",
                                $total_leagues,
                                $league_display,
                                $league_link
                            );
                            break;
                        case STATE_SELECT_HOMETEAM:
                            $total_teams = pg_fetch_assoc(
                                pg_query($db, "SELECT COUNT(*) AS teams FROM team;")
                            )["teams"];
                            create_paginated_select_form(
                                "SELECT * FROM team ORDER BY longname LIMIT $1 OFFSET $2",
                                $total_teams,
                                $team_display,
                                $hometeam_link
                            );
                            break;
                        case STATE_SELECT_AWAYTEAM:
                            $total_teams = pg_fetch_assoc(
                                pg_query($db, "SELECT COUNT(*) AS teams FROM team;")
                            )["teams"];
                            create_paginated_select_form(
                                "SELECT * FROM team ORDER BY longname LIMIT $1 OFFSET $2",
                                $total_teams,
                                $team_display,
                                $awayteam_link
                            );
                            break;
                        }

                        if( $state == STATE_COMPLETE ): ?>
                    <form method="POST" class="controlpanel-form">
                        <input type="hidden" name="league" value="<?php echo $_GET["league"]; ?>" />
                        <input type="hidden" name="awayteam" value="<?php echo $_GET["awayteam"]; ?>" />
                        <input type="hidden" name="hometeam" value="<?php echo $_GET["hometeam"]; ?>" />
                        
                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Home team Goals</label>
                                    <div class="control">
                                        <input class="input" type="numeric" name="hometeam_goals" />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Season</label>
                                    <div class="control">
                                        <input class="input" type="text" name="season" />
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Away team Goals</label>
                                    <div class="control">
                                        <input class="input" type="numeric" name="awayteam_goals" />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Stage</label>
                                    <div class="control">
                                        <input class="input" type="text" name="stage" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Played On</label>
                            <div class="control">
                                <input class="input" type="date" name="played_on" />
                            </div>
                        </div>

                        <div class="field">
                            <div class="control">
                                <input class="input button is-link" type="submit" value="Insert data" />
                            </div>
                        </div>
                        <?php
                            if( isset($success) ){
                                show_success_message("Match added successfully");
                            }
                            if( isset($error) ){
                                show_message_on_error($error);
                            }
                        ?>
                    </form>
                    <?php
                        endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>