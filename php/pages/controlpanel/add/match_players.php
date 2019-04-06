<?php
    require_once('config.php');
    require_once(LIB . '/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/match.php');
    require_once(LIB . '/models/team.php');
    require_once(LIB . '/models/loggeduser.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/paginated-select.php');
    require_once(COMPONENTS . '/messages.php');

    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }
    if( LoggedUser::getRole() == 'partner' ){
        redirect(PAGE_FORBIDDEN);
        exit();
    }
    $db = db_connect();
    Match::prepare($db);

    // This page also works as an API endpoint, returning search results for player
    if( isset($_GET["api"]) && $_GET["api"] == 1 && isset($_GET["action"]) ){
        if( $_GET["action"] == "search" && isset($_GET["query"]) ){
            pg_prepare($db, "player_query", "SELECT * FROM player WHERE lower(name) LIKE $1 ORDER BY name ASC LIMIT 5");
            $result = pg_execute(
                $db, 
                "player_query", 
                array(
                    strtolower("%" . $_GET["query"] . "%")
                )
            );
            $players = array();
            while( $player = pg_fetch_assoc($result) ){
                array_push($players, array(
                    "id" => $player["id"],
                    "name" => $player["name"]
                ));
            }
            echo json_encode(array(
                "success" => true,
                "result" => $players
            ));
        } else if( $_GET["action"] == "status" && isset($_GET["match"]) ) {
            $match = Match::find($db, $_GET["match"]);
            pg_prepare(
                $db, 
                "played_query", 
                "SELECT player, name FROM played 
                 JOIN player ON player.id = played.player 
                 WHERE match = $1 AND team = $2;"
            );
            
            $result = pg_execute($db, "played_query", array($_GET["match"], $match["hometeam"]));
            $hometeam_player = array();
            while($p = pg_fetch_assoc($result) ){
                array_push($hometeam_player, array(
                    "id" => $p["player"],
                    "name" => $p["name"]
                ));
            }

            $result = pg_execute($db, "played_query", array($_GET["match"], $match["awayteam"]));
            $awayteam_player = array();
            while($p = pg_fetch_assoc($result) ){
                array_push($awayteam_player, array(
                    "id" => $p["player"],
                    "name" => $p["name"]
                ));
            }

            echo json_encode(array(
                "success" => true,
                "result" => array(
                    "hometeam" => $hometeam_player,
                    "awayteam" => $awayteam_player
                )
            ));
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "There are missing parameters in your request. If 'action' = 'search' then you also need a 'query' parameter. If 'action' = 'status' you also need a 'match' parameter"
            ));
        }
        exit(0);
    }

    if( isset($_GET["id"]) ){
        Team::prepare($db);

        $match = Match::find($db, $_GET["id"]);
        if( $match != NULL ){
            if( $match["created_by"] == LoggedUser::getId() ){
                $hometeam = Team::find($db, $match["hometeam"]);
                $awayteam = Team::find($db, $match["awayteam"]);
            } else {
                $error = "You cannot edit this match players. Only the user who added this match to the database can add players";
            }
        } else {
            $error = "There is no match with that id";
        }
    } else {
        $error = "The URL is missing the match id. You might have followed a bad link or copied the link wrong";
    }
    
    if( isset($_POST["match"]) && isset($_POST["hometeam"]) && isset($_POST["awayteam"]) ){
        pg_prepare($db, "delete_played", "DELETE FROM played WHERE match = $1 AND team = $2;");
        pg_execute($db, "delete_played", array($_POST["match"], $_POST["hometeam"]));
        pg_execute($db, "delete_played", array($_POST["match"], $_POST["awayteam"]));
        pg_prepare($db, "add_played", "INSERT INTO played(player, match, team) VALUES ($1, $2, $3);");

        if( isset($_POST["hometeam_player"]) ){
            for($i = 0; $i < sizeof($_POST["hometeam_player"]); $i++){
                pg_execute(
                    $db, 
                    "add_played", 
                    array(
                        $_POST["hometeam_player"][$i], 
                        $_POST["match"], 
                        $_POST["hometeam"]
                ));
            }
        }
        if( isset($_POST["awayteam_player"]) ){
            for($i = 0; $i < sizeof($_POST["awayteam_player"]); $i++){
                pg_execute(
                    $db, 
                    "add_played", 
                    array(
                        $_POST["awayteam_player"][$i], 
                        $_POST["match"], 
                        $_POST["awayteam"]
                ));
            }
        }
        $success = true;
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
                    <h2 class="title is-2 title-centered">Select the players who played in the match</h2>
                    <?php
                        if( !isset($error) ):
                            ?>
                        <div class="columns">
                            <div class="column">
                                <nav class="panel">
                                    <p class="panel-heading">
                                        search a player
                                    </p>
                                    <div class="panel-block">
                                        <p class="control">
                                            <input class="input" type="text" placeholder="search" id="search-input">
                                        </p>
                                        <p class="control">
                                            <button class="button is-outlined" onclick="search_players();">
                                                <span class="icon is-small is-left">
                                                    <i class="fas fa-search" aria-hidden="true"></i>
                                                </span>
                                                Search players
                                            </button>
                                        </p>
                                    </div>
                                    <div id="search-results"></div>
                                    <div class="panel-block">
                                        <button class="button is-link is-outlined" id="button-addhometeam">
                                            add selected to hometeam
                                        </button>
                                        <button class="button is-link is-outlined" id="button-addawayteam">
                                            add selected to awayteam
                                        </button>
                                    </div>
                                </nav>
                            </div>
                            <form class="column" method="POST">
                                <input id="match_id" type="hidden" name="match" value="<?php echo $match["id"]; ?>" />
                                <input type="hidden" name="hometeam" value="<?php echo $hometeam["id"]; ?>" />
                                <input type="hidden" name="awayteam" value="<?php echo $awayteam["id"]; ?>" />
                                <div class="columns">
                                    <div class="column">
                                        <h3 class="title is-3 title-centered">
                                            <?php echo $hometeam["longname"]; ?>
                                        </h3>
                                        <div class="player-list" id="hometeam-players"></div> 
                                    </div>
                                    <div class="column">
                                        <h3 class="title is-3 title-centered">
                                            <?php echo $awayteam["longname"]; ?>
                                        </h3>
                                        <div class="player-list" id="awayteam-players"></div>
                                    </div>
                                </div>
                                <input type="submit" class="button is-link is-fullwidth is-outlined" />
                            </form>
                        </div>
                    <?php
                        endif;
                    ?>
                    <?php
                        if( isset($success) ){
                            create_message("Player participation successfully updated", MSG_SUCCESS);
                        }
                        if( isset($error) ){
                            create_message($error, MSG_ERROR);
                        }
                    ?>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="<?php echo JS; ?>/match_players.js"></script>
    </body>
</html>