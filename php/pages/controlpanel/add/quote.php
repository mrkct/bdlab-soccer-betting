<?php
    require_once('config.php');
    require_once(LIB . '/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/match.php');
    require_once(LIB . '/models/quote.php');
    require_once(LIB . '/models/team.php');
    require_once(LIB . '/models/league.php');
    require_once(LIB . '/models/loggeduser.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/paginated-select.php');
    require_once(COMPONENTS . '/messages.php');
    
    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }
    if( LoggedUser::getRole() == 'operator' ){
        redirect(PAGE_FORBIDDEN);
        exit();
    }

    $db = db_connect();

    define("STATE_SELECT_LEAGUE", "select_league");
    define("STATE_SELECT_SEASON", "select_season");
    define("STATE_SELECT_MATCH", "select_match");
    define("STATE_COMPLETE", "complete");
    $valid_states = array(
        STATE_SELECT_LEAGUE,
        STATE_SELECT_SEASON,
        STATE_SELECT_MATCH,
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
        case STATE_SELECT_SEASON:
            if( !isset($_GET["league"]) ){
                $state = STATE_SELECT_LEAGUE;
            }
            break;
        case STATE_SELECT_MATCH:
            if( !isset($_GET["league"]) || !isset($_GET["season"]) ){
                $state = STATE_SELECT_LEAGUE;
            }
            break;
        case STATE_COMPLETE:
            if( !isset($_GET["match"]) ){
                $state = STATE_SELECT_LEAGUE;
            }
            break;
        }
    }

    if( isset($_GET["match"]) ){
        Match::prepare($db);
        Team::prepare($db);
        League::prepare($db);
        $match = Match::find($db, $_GET["match"]);
        $league = League::findById($db, $match["league"]);
        $hometeam = Team::find($db, $match["hometeam"]);
        $awayteam = Team::find($db, $match["awayteam"]);
    }

    if( isset($_POST["match"]) && isset($_POST["home_quote"]) && isset($_POST["draw_quote"]) && isset($_POST["away_quote"]) ){
        try{
            Quote::prepare($db);
            Quote::insert(
                $db,
                $_POST["match"],
                LoggedUser::getAffiliation(),
                $_POST["home_quote"],
                $_POST["draw_quote"],
                $_POST["away_quote"]
            );
            $success = true;
        }catch(PermissionDeniedException $e){
            $error = "You are not allowed to insert match quotes";
        }catch(DuplicateDataException $e){
            $error = "That quote is already in the databse";
        }catch(ForeignKeyException $e){
            $error = "There was an error with the selected match. Reload this page and retry";
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
                            echo "Select the league the match to add a quote to was played in";
                            break;
                        case STATE_SELECT_SEASON:
                            echo "Select the season the match to add a quote to was played in";
                            break;
                        case STATE_SELECT_MATCH:
                            echo "Select the match to add a quote to";
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
                            return sprintf("?state=%s&league=%d", STATE_SELECT_SEASON, $item["id"]);
                        };

                        $season_display = function($item){
                            return $item["season"];
                        };

                        $season_link = function($item){
                            return sprintf("?state=%s&league=%d&season=%s", STATE_SELECT_MATCH, $_GET["league"], $item["season"]);
                        };

                        $match_display = function($item){
                            return sprintf(
                                "%s: %s vs %s, finished %d - %d", 
                                $item["played_on"], 
                                $item["hometeam_longname"], 
                                $item["awayteam_longname"], 
                                $item["hometeam_goals"], 
                                $item["awayteam_goals"]
                            );
                        };

                        $match_link = function($item){
                            return sprintf("?state=%s&match=%d", STATE_COMPLETE, $item["id"]);
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
                        case STATE_SELECT_SEASON:
                            pg_prepare($db, "get_total_seasons", "SELECT COUNT(DISTINCT season) AS seasons FROM match WHERE league = $1;");
                            $total_seasons = pg_fetch_assoc(
                                pg_execute(
                                    $db, 
                                    "get_total_seasons", 
                                    array($_GET["league"])
                                )
                            )["seasons"];
                            create_paginated_select_form(
                                "SELECT DISTINCT season FROM match WHERE league = $1 ORDER BY season DESC LIMIT $2 OFFSET $3",
                                $total_seasons,
                                $season_display,
                                $season_link,
                                array($_GET["league"])
                            );
                            break;
                        case STATE_SELECT_MATCH:
                            pg_prepare($db, "get_total_matches", "SELECT COUNT(*) AS matches FROM match WHERE league = $1 AND season = $2;");
                            $total_matches = pg_fetch_assoc(
                                pg_execute(
                                    $db, 
                                    "get_total_matches", 
                                    array($_GET["league"], $_GET["season"])
                                )
                            )["matches"];
                            create_paginated_select_form(
                                "SELECT 
                                    match.*, 
                                    t1.shortname as hometeam_shortname,
                                    t1.longname as hometeam_longname,
                                    t2.shortname as awayteam_shortname,
                                    t2.longname as awayteam_longname
                                FROM match 
                                JOIN team AS t1 ON t1.id = match.hometeam
                                JOIN team as t2 ON t2.id = match.awayteam
                                WHERE league = $1 AND season = $2 
                                ORDER BY played_on 
                                LIMIT $3 OFFSET $4",
                                $total_matches,
                                $match_display,
                                $match_link,
                                array($_GET["league"], $_GET["season"])
                            );
                            break;
                        }

                        if( $state == STATE_COMPLETE ): ?>
                    <form method="POST" class="controlpanel-form">
                        <div class="notification is-primary">
                            <strong>Match ID: </strong><?php echo $match["id"]; ?><br>
                            <strong>Played on: </strong><?php echo format_date($match["played_on"]); ?><br>
                            <strong>League: </strong><?php echo $league["name"]; ?><br>
                            <strong>Season: </strong><?php echo $match["season"]; ?>  <strong>Stage: </strong><?php echo $match["stage"]; ?><br>
                            <strong>Teams: </strong><?php echo $hometeam["longname"], " - ", $awayteam["longname"]; ?><br>
                        </div>
                        <input type="hidden" name="match" value="<?php echo $match["id"]; ?>" />

                        <div class="field">
                            <label class="label">Home team wins quote</label>
                            <div class="control">
                                <input class="input" type="numeric" name="home_quote" min=0 />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Match draw quote</label>
                            <div class="control">
                                <input class="input" type="numeric" name="draw_quote" min=0 />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Away team wins quote</label>
                            <div class="control">
                                <input class="input" type="numeric" name="away_quote" min=0 />
                            </div>
                        </div>

                        <?php
                            if( LoggedUser::getAffiliation() != NULL ): ?>
                                <div class="field">
                                    <div class="control">
                                        <input class="input button is-link" type="submit" value="Insert data" />
                                    </div>
                                </div>
                        <?php
                            else:
                                create_message(
                                    "<strong>Warning: </strong>You are not affiliated with a bet provider. 
                                    You can see this page as you're an administrator but cannot add quotes 
                                    for a betting society on their behalf.", 
                                    MSG_WARNING
                                );
                            endif; 

                            if( isset($success) ){
                                create_message("Match quote added successfully", MSG_SUCCESS);
                            }
                            if( isset($error) ){
                                create_message($error, MSG_ERROR);
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