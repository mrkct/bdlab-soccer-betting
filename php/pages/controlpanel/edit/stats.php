<?php
    require_once('config.php');
    require_once(LIB .'/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/player.php');
    require_once(LIB . '/models/stats.php');
    require_once(COMPONENTS . '/paginated-select.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/messages.php');


    define('STATE_SELECT_PLAYER', 'select_player');
    define('STATE_SELECT_STATS', 'select_stats');
    define('STATE_EDIT', 'edit');
    define('ACTION_DELETE', 'delete');
    define('ACTION_EDIT', 'edit');

    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }
    
    $db = db_connect();
    $state = STATE_SELECT_PLAYER;
    $stats = NULL;
    $player = NULL;
    if( isset($_GET["player"]) ){
        Player::prepare($db);
        $player = Player::find($db, $_GET["player"]);
        $state = STATE_SELECT_STATS;
        if( isset($_GET["attribute_date"]) ){
            Stats::prepare($db);
            $state = STATE_EDIT;
            $stats = Stats::find($db, $_GET["player"], $_GET["attribute_date"]);
            if( $stats == null ){
                $error = "You might have followed a bad URL";
            }
        }
        if( $player == null ){
            $error = "You might have followed a bad URL";
        }
    }

    if( are_set(["action", "player", "attribute_date"], $_POST) ){
        Stats::prepare($db);
        if( $_POST["action"] == ACTION_DELETE ){
            try{
                Stats::delete($db, $_POST["player"], $_POST["attribute_date"]);
                $success = true;
                $deleted = true;
            }catch(PermissionDeniedException $e){
                $error = "You are not allowed to delete players' stats relevations";
            }catch(DBException $e){
                $error = "An unknown error occurred[" . $e->getMessage() . "]";
            }
            
        } else if ( $_POST["action"] == ACTION_EDIT ){
            if( are_set(["player", "old_attribute_date", "attribute_date", "overall_rating", 
                "potential", "preferred_foot", 
                "attacking_work_rate", "defensive_work_rate", "crossing", "finishing", 
                "heading_accuracy", "short_passing", "volleys", "dribbling", "curve", 
                "free_kick_accuracy", "long_passing", "ball_control", "acceleration", 
                "sprint_speed", "agility", "reactions", "balance", "shot_power", "jumping", 
                "stamina", "strength", "long_shots", "aggression", "interceptions", "positioning", 
                "vision", "penalties", "marking", "standing_tackle", "sliding_tackle", "gk_diving", 
                "gk_handling", "gk_kicking", "gk_positioning", "gk_reflexes"], $_POST) )
            {
                try{
                    $stats = Stats::edit(
                        $db,
                        read_param($_POST["player"]),
                        read_param($_POST["old_attribute_date"]),
                        read_param($_POST["attribute_date"]),
                        read_param($_POST["overall_rating"]),
                        read_param($_POST["potential"]),
                        read_param($_POST["preferred_foot"]),
                        read_param($_POST["attacking_work_rate"]),
                        read_param($_POST["defensive_work_rate"]),
                        read_param($_POST["crossing"]),
                        read_param($_POST["finishing"]),
                        read_param($_POST["heading_accuracy"]),
                        read_param($_POST["short_passing"]),
                        read_param($_POST["volleys"]),
                        read_param($_POST["dribbling"]),
                        read_param($_POST["curve"]),
                        read_param($_POST["free_kick_accuracy"]),
                        read_param($_POST["long_passing"]),
                        read_param($_POST["ball_control"]),
                        read_param($_POST["acceleration"]),
                        read_param($_POST["sprint_speed"]),
                        read_param($_POST["agility"]),
                        read_param($_POST["reactions"]),
                        read_param($_POST["balance"]),
                        read_param($_POST["shot_power"]),
                        read_param($_POST["jumping"]),
                        read_param($_POST["stamina"]),
                        read_param($_POST["strength"]),
                        read_param($_POST["long_shots"]),
                        read_param($_POST["aggression"]),
                        read_param($_POST["interceptions"]),
                        read_param($_POST["positioning"]),
                        read_param($_POST["vision"]),
                        read_param($_POST["penalties"]),
                        read_param($_POST["marking"]),
                        read_param($_POST["standing_tackle"]),
                        read_param($_POST["sliding_tackle"]),
                        read_param($_POST["gk_diving"]),
                        read_param($_POST["gk_handling"]),
                        read_param($_POST["gk_kicking"]),
                        read_param($_POST["gk_positioning"]),
                        read_param($_POST["gk_reflexes"])
                    );
                    $success = true;
                }catch(PermissionDeniedException $e){
                    $error = "You are not allowed to edit players data";
                }catch(DuplicateDataException $e){
                    $error = "There is already a player with that id";
                }catch(DBException $e){
                    $error = "An unknown error occurred[" . $e->getMessage() . "]";
                }
            } else {
                echo "  nope<br>";
                print_r($_POST);
                echo "<br>";
            }
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
                    <?php 
                        if ( isset($success) ){
                            if( $_POST["action"] == ACTION_DELETE ){
                                create_message("Player's stats relevation successfully deleted", MSG_SUCCESS);
                            } else if( $_POST["action"] == ACTION_EDIT ){
                                create_message("Player's data updated successfully", MSG_SUCCESS);
                            }
                        }
                        if( isset($error) ){
                            create_message($error, MSG_ERROR);
                        }
                    ?>
                    <?php
                        if( $state == STATE_SELECT_PLAYER ){
                            ?>
                            <h2 class="title is-2 title-centered">Select the player whose stats relevation you want to edit</h2>
                            <?php
                            $display_player = function($item){
                                return sprintf("%s", $item["name"]);
                            };
                            $link_player = function($item){
                                return sprintf("?player=%d", $item["player"]);
                            };
                            $total_players = pg_fetch_assoc(
                                pg_query(
                                    $db, 
                                    "SELECT COUNT(DISTINCT player) AS players FROM stats;"
                                )
                            )["players"];
                            create_paginated_select_form(
                                "SELECT DISTINCT player, name 
                                 FROM stats 
                                 LEFT JOIN player ON player.id = stats.player 
                                 ORDER BY name
                                 LIMIT $1 OFFSET $2;", 
                                $total_players, 
                                $display_player,
                                $link_player
                            );
                        }
                    ?>
                    <?php
                        if( $player != NULL && $state == STATE_SELECT_STATS ){
                            ?> 
                            <h2 class="title is-2 title-centered">Select the stats relevation to edit</h2> 
                            <?php 
                            $display_stats = function($item){
                                return sprintf("%s - Overall Rating: %d", format_date($item["attribute_date"]), $item["overall_rating"]);
                            };
                            $link_stats = function($item){
                                return sprintf("?player=%d&attribute_date=%s", $item["player"], $item["attribute_date"]);
                            };
                            pg_prepare($db, "get_total_stats", "SELECT COUNT(*) AS relevations FROM stats WHERE player = $1;");
                            $total_stats = pg_fetch_assoc(
                                pg_execute(
                                    $db, 
                                    "get_total_stats", 
                                    array($_GET["player"])
                                )
                            )["relevations"];
                            create_paginated_select_form(
                                "SELECT player, attribute_date, overall_rating 
                                 FROM stats 
                                 WHERE player = $1 
                                 ORDER BY attribute_date 
                                 LIMIT $2 OFFSET $3;", 
                                $total_stats, 
                                $display_stats,
                                $link_stats,
                                array($_GET["player"])
                            );
                        } 
                    ?>
                    <?php
                        if( $stats != NULL && $state == STATE_EDIT ): ?>
                            <h2 class="title is-2 title-centered">Edit Player Stats</h2>
                            <form method="POST" 
                                action="<?php echo sprintf("?player=%d&attribute_date=%s", $stats["player"], $stats["attribute_date"]); ?>">
                                <input type="hidden" name="action" value="<?php echo ACTION_EDIT; ?>" />
                                <input type="hidden" name="player" value="<?php echo $stats["player"]; ?>" />
                                <input type="hidden" name="old_attribute_date" value="<?php echo $stats["attribute_date"]; ?>" />

                                <div class="columns">
                                <div class="column">
                                    <div class="field">
                                        <label class="label">Date</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="date" 
                                                name="attribute_date"
                                                value="<?php echo $stats["attribute_date"]; ?>" required />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Overall rating</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="overall_rating" 
                                                value="<?php echo $stats["overall_rating"]; ?>" required />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Potential</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="potential" 
                                                value="<?php echo $stats["potential"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Preferred Foot</label>
                                        <div class="control">
                                            <label class="radio">
                                                <input 
                                                    type="radio" 
                                                    name="preferred_foot" 
                                                    value="left" 
                                                    <?php echo $stats["preferred_foot"] == "left" ? "checked": "" ?> />
                                                Left
                                            </label>
                                            <label class="radio">
                                                <input 
                                                    type="radio" 
                                                    name="preferred_foot" 
                                                    value="right" 
                                                    <?php echo $stats["preferred_foot"] == "right" ? "checked": "" ?> />
                                                Right
                                            </label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Attacking Work Rate</label>
                                        <div class="control">
                                            <input type="hidden" name="attacking_work_rate" value="" />
                                            <label class="radio">
                                                <input 
                                                    type="radio" 
                                                    name="attacking_work_rate" 
                                                    value="low" 
                                                    <?php echo $stats["attacking_work_rate"] == "low" ? "checked": "" ?> />
                                                Low
                                            </label>
                                            <label class="radio">
                                                <input 
                                                    type="radio" 
                                                    name="attacking_work_rate" 
                                                    value="medium" 
                                                    <?php echo $stats["attacking_work_rate"] == "medium" ? "checked": "" ?> />
                                                Medium
                                            </label>
                                            <label class="radio">
                                                <input 
                                                    type="radio" 
                                                    name="attacking_work_rate" 
                                                    value="high"
                                                    <?php echo $stats["attacking_work_rate"] == "high" ? "checked": "" ?>  />
                                                High
                                            </label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Defensive Work Rate</label>
                                        <div class="control">
                                            <input type="hidden" name="defensive_work_rate" value="" />
                                            <label class="radio">
                                                <input 
                                                    type="radio" 
                                                    name="defensive_work_rate" 
                                                    value="low"
                                                    <?php echo $stats["defensive_work_rate"] == "low" ? "checked": "" ?>  />
                                                Low
                                            </label>
                                            <label class="radio">
                                                <input 
                                                    type="radio" 
                                                    name="defensive_work_rate" 
                                                    value="medium"
                                                    <?php echo $stats["defensive_work_rate"] == "medium" ? "checked": "" ?> />
                                                Medium
                                            </label>
                                            <label class="radio">
                                                <input 
                                                    type="radio" 
                                                    name="defensive_work_rate" 
                                                    value="high"
                                                    <?php echo $stats["defensive_work_rate"] == "high" ? "checked": "" ?> />
                                                High
                                            </label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Marking</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="marking"
                                                value="<?php echo $stats["marking"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Standing Tackle</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="standing_tackle"
                                                value="<?php echo $stats["standing_tackle"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Free Kick Accuracy</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="free_kick_accuracy" 
                                                value="<?php echo $stats["free_kick_accuracy"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Long Passing</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="long_passing" 
                                                value="<?php echo $stats["long_passing"]; ?>"  />
                                        </div>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="field">
                                        <label class="label">Crossing</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="crossing" 
                                                value="<?php echo $stats["crossing"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Finishing</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="finishing" 
                                                value="<?php echo $stats["finishing"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Heading Accuracy</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="heading_accuracy" 
                                                value="<?php echo $stats["heading_accuracy"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Short Passing</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="short_passing" 
                                                value="<?php echo $stats["short_passing"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Volleys</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="volleys" 
                                                value="<?php echo $stats["volleys"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Dribbling</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="dribbling" 
                                                value="<?php echo $stats["dribbling"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Curve</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="curve" 
                                                value="<?php echo $stats["dribbling"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Sliding Tackle</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="sliding_tackle" 
                                                value="<?php echo $stats["sliding_tackle"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Vision</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="vision" 
                                                value="<?php echo $stats["vision"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Penalties</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="penalties" 
                                                value="<?php echo $stats["penalties"]; ?>"  />
                                        </div>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="field">
                                        <label class="label">Sprint Speed</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="sprint_speed" 
                                                value="<?php echo $stats["sprint_speed"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Agility</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="agility" 
                                                value="<?php echo $stats["agility"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Reactions</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="reactions" 
                                                value="<?php echo $stats["reactions"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Balance</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="balance" 
                                                value="<?php echo $stats["balance"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Shot Power</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="shot_power" 
                                                value="<?php echo $stats["shot_power"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Jumping</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="jumping" 
                                                value="<?php echo $stats["jumping"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Stamina</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="stamina" 
                                                value="<?php echo $stats["stamina"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Strength</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="strength" 
                                                value="<?php echo $stats["strength"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Long Shots</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="long_shots" 
                                                value="<?php echo $stats["long_shots"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Aggression</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="aggression" 
                                                value="<?php echo $stats["aggression"]; ?>"  />
                                        </div>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="field">
                                        <label class="label">Ball Control</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="ball_control" 
                                                value="<?php echo $stats["ball_control"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Acceleration</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="acceleration" 
                                                value="<?php echo $stats["acceleration"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Interceptions</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="interceptions" 
                                                value="<?php echo $stats["interceptions"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Positioning</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="positioning" 
                                                value="<?php echo $stats["positioning"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Diving (Goalkeeper)</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="gk_diving" 
                                                value="<?php echo $stats["gk_diving"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Handling (Goalkeeper)</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="gk_handling" 
                                                value="<?php echo $stats["gk_handling"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Kicking (Goalkeeper)</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="gk_kicking" 
                                                value="<?php echo $stats["gk_kicking"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Positioning (Goalkeeper)</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="gk_positioning" 
                                                value="<?php echo $stats["gk_positioning"]; ?>"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Reflexes (Goalkeeper)</label>
                                        <div class="control">
                                            <input 
                                                class="input" 
                                                type="number" 
                                                min=0 max=100 
                                                name="gk_reflexes" 
                                                value="<?php echo $stats["gk_reflexes"]; ?>"  />
                                        </div>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="field">
                                        <div class="control">
                                            <?php
                                                if( !isset($success) || !isset($deleted) ): ?>
                                                    <input class="input button is-link" type="submit" value="Update Data" />
                                                    <button type="button" class="button is-danger modal-toggle delete-button">Delete Player</button>
                                            <?php
                                                endif; ?>
                                            <?php
                                                if( isset($success) ): ?>
                                                    <a class="button is-primary restart-button" href="?player=<?php echo $player["id"]; ?>">
                                                        Edit another stats relevation
                                                    </a>
                                            <?php
                                                endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                    <?php
                        endif; ?>
                </div>
            </div>
        </div>
        <div class="modal" id="modal-delete-warning">
            <div class="modal-background modal-toggle"></div>
            <div class="modal-content">
                <article class="message">
                    <div class="message-body">
                        <h2 class="title is-2">
                            Do you really want to delete this stats relevation from the database?
                        </h2>
                        <p class="paragraph">
                            This means that this player won't have stats for this date. If there is not another stats
                            relevation to use it means this player will be excluded from the MVP vote in games he played in.
                            <strong>This is irreversible!</strong>
                        </p>
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo ACTION_DELETE; ?>" />
                            <input type="hidden" name="player" value="<?php echo $stats["player"]; ?>" />
                            <input type="hidden" name="attribute_date" value="<?php echo $stats["attribute_date"]; ?>" />
                        
                            <input type="submit" class="button is-danger" value="Yes, delete it" />
                        </form>
                    </div>
                </article>
            </div>
            <button class="modal-close is-large modal-toggle" aria-label="close"></button>
        </div>
        <script type="text/javascript" src="<?php echo JS; ?>/modal-toggle.js"></script>
    </body>
</html>