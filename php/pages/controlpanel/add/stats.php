<?php
    require_once('config.php');
    require_once(LIB . '/utils.php');
    require_once(LIB . '/models/player.php');
    require_once(LIB . '/models/stats.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/error_message.php');
    
    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }
    
    function read_param($param){
        return isset($param) && !empty($param)? $param : NULL;
    }

    require_once(LIB . '/database.php');
    $db = db_connect();
    $player = null;
    if( isset($_GET["player_id"]) ){
        Player::prepare($db);
        $player = Player::find($db, $_GET["player_id"]);
        
        if( isset($_POST["player_id"]) && isset($_POST["date"]) ){
            try{
                Stats::prepare($db);
                Stats::insert(
                    $db,
                    read_param($_POST["player_id"]),
                    read_param($_POST["date"]),
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
            }catch(PermissionDeniedException $e){
                $error = "You are not allowed to insert stats data";
            }catch(DuplicateDataException $e){
                $error = "There are already stats for that player and that date";
            }catch(ForeignKeyException $e){
                $error = "There is no player with that id";;
            }catch(DBException $e){
                $error = "An unknown error occurred[" . $e->getMessage() . "]";
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
                    <?php if( $player ): ?>
                        <h2 class="title is-2 title-centered">Add Stats For <?php echo $player["name"]; ?></h2>
                        <form method="POST">
                            <div class="columns">
                                <div class="column">
                                    <input class="input" type="hidden" name="player_id" value="<?php echo $_GET["player_id"]; ?>" readonly />
                                    <div class="field">
                                        <label class="label">Date</label>
                                        <div class="control">
                                            <input class="input" type="date" name="date" required />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Overall rating</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="overall_rating" required />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Potential</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="potential"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Preferred Foot</label>
                                        <div class="control">
                                            <label class="radio">
                                                <input type="radio" name="preferred_foot" value="left" />
                                                Left
                                            </label>
                                            <label class="radio">
                                                <input type="radio" name="preferred_foot" value="right" />
                                                Right
                                            </label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Attacking Work Rate</label>
                                        <div class="control">
                                            <label class="radio">
                                                <input type="radio" name="attacking_work_rate" value="low" />
                                                Low
                                            </label>
                                            <label class="radio">
                                                <input type="radio" name="attacking_work_rate" value="medium" />
                                                Medium
                                            </label>
                                            <label class="radio">
                                                <input type="radio" name="attacking_work_rate" value="high" />
                                                High
                                            </label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Defensive Work Rate</label>
                                        <div class="control">
                                            <label class="radio">
                                                <input type="radio" name="defensive_work_rate" value="low" />
                                                Low
                                            </label>
                                            <label class="radio">
                                                <input type="radio" name="defensive_work_rate" value="medium" />
                                                Medium
                                            </label>
                                            <label class="radio">
                                                <input type="radio" name="defensive_work_rate" value="high" />
                                                High
                                            </label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Marking</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="marking"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Standing Tackle</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="standing_tackle"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Free Kick Accuracy</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="free_kick_accuracy"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Long Passing</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="long_passing"  />
                                        </div>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="field">
                                        <label class="label">Crossing</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="crossing"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Finishing</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="finishing"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Heading Accuracy</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="heading_accuracy"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Short Passing</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="short_passing"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Volleys</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="volleys"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Dribbling</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="dribbling"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Curve</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="curve"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Sliding Tackle</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="sliding_tackle"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Vision</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="vision"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Penalties</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="penalties"  />
                                        </div>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="field">
                                        <label class="label">Sprint Speed</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="sprint_speed"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Agility</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="agility"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Reactions</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="reactions"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Balance</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="balance"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Shot Power</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="shot_power"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Jumping</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="jumping"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Stamina</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="stamina"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Strength</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="strength"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Long Shots</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="long_shots"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Aggression</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="aggression"  />
                                        </div>
                                    </div>




                                </div>
                                <div class="column">
                                    <div class="field">
                                        <label class="label">Ball Control</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="ball_control"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Acceleration</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="acceleration"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Interceptions</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="interceptions"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Positioning</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="positioning"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Diving (Goalkeeper)</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="gk_diving"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Handling (Goalkeeper)</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="gk_handling"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Kicking (Goalkeeper)</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="gk_kicking"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Positioning (Goalkeeper)</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="gk_positioning"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Reflexes (Goalkeeper)</label>
                                        <div class="control">
                                            <input class="input" type="number" min=0 max=100 name="gk_reflexes"  />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="control">
                                            <input class="input button is-link" type="submit" value="Insert data" />
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    if ( isset($success) ): ?>
                                        <div class="notification is-success">
                                            Stats successfully added
                                        </div>
                                <?php endif; ?>
                                <?php
                                    if( isset($error) ){
                                        show_message_on_error($error);
                                    }
                                ?>
                            </div>
                        </form>
                    <?php endif; ?>
                    <?php if( !$player ): ?>
                        <h2 class="title is-2 title-centered">Select the player to add stats to</h2>
                        <?php
                            $player_display = function ($item){
                                return $item["name"];
                            };

                            $player_link = function ($item){
                                return "?player_id=" . $item["id"];
                            };

                            if( !$player ){
                                require_once(COMPONENTS . '/paginated-select.php');
                                $total_players = pg_fetch_assoc(
                                    pg_query($db, "SELECT COUNT(*) AS players FROM player;")
                                )["players"];
                                create_paginated_select_form(
                                    "SELECT * FROM player ORDER BY name LIMIT $1 OFFSET $2",
                                    $total_players,
                                    $player_display,
                                    $player_link
                                );
                            }
                        ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>