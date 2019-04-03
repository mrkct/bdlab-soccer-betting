<?php
    require_once('config.php');
    require_once(LIB .'/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/player.php');
    require_once(COMPONENTS . '/paginated-select.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/error_message.php');


    define('STATE_SELECT', 'select');
    define('STATE_EDIT', 'edit');
    define('ACTION_DELETE', 'delete');
    define('ACTION_EDIT', 'edit');

    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }
        
    $state = STATE_SELECT;
    $db = db_connect();
    if( isset($_GET["id"]) ){
        $state = STATE_EDIT;
        Player::prepare($db);
        $player = Player::find($db, $_GET["id"]);
        if( $player == null ){
            $error = "You might have followed a bad URL";
        }
    }

    if( isset($_POST["action"]) && isset($_POST["id"]) ){
        if( $_POST["action"] == ACTION_DELETE ){
            try{
                Player::delete($db, $_POST["id"]);
                $success = true;
            }catch(PermissionDeniedException $e){
                $error = "You are not allowed to delete players data";
            }catch(DBException $e){
                $error = "An unknown error occurred[" . $e->getMessage() . "]";
            }
            
        } else if ( $_POST["action"] == ACTION_EDIT ){
            if( isset($_POST["longname"]) && isset($_POST["shortname"]) ){
                try{
                    // TODO: Edit the player data
                    // Player::edit($db, ...)
                    $success = true;
                }catch(PermissionDeniedException $e){
                    $error = "You are not allowed to edit teams data";
                }catch(DuplicateDataException $e){
                    $error = "There is already a team with that id";
                }catch(DBException $e){
                    $error = "An unknown error occurred[" . $e->getMessage() . "]";
                }
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
                        if( $state == STATE_SELECT ){
                            ?> 
                            <h2 class="title is-2 title-centered">Select the player to edit</h2> 
                            <?php 
                            $display_player = function($item){
                                return $item["name"];
                            };
                            $link_player = function($item){
                                return "?id=" . $item["id"];
                            };

                            $total_players = pg_fetch_assoc(pg_query($db, "SELECT COUNT(*) AS players FROM player;"))["players"];
                            create_paginated_select_form(
                                "SELECT * FROM player ORDER BY name LIMIT $1 OFFSET $2", 
                                $total_players, 
                                $display_player,
                                $link_player
                            );
                        } 
                    ?>
                    <?php
                        if( $state == STATE_EDIT ): ?>
                            <h2 class="title is-2 title-centered">Edit Player</h2>
                            <form method="POST" class="controlpanel-form">
                                <input type="hidden" name="action" value="<?php echo ACTION_EDIT; ?>" />
                                <div class="field">
                                    <label class="label">Player's ID</label>
                                    <div class="control">
                                        <input class="input" name="id" type="numeric" value="<?php echo $player["id"]; ?>"/>
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Name of the player</label>
                                    <div class="control">
                                        <input class="input" name="name" value="<?php echo $player["name"]; ?>" required />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Birthday of the player</label>
                                    <div class="control">
                                        <input class="input" type="date" name="birthday" value="<?php echo $player["birthday"]; ?>" required />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Height of the player(in cm)</label>
                                    <div class="control">
                                        <input class="input" type="numeric" name="height" value="<?php echo $player["height"]; ?>" required />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Weight of the player(in lb)</label>
                                    <div class="control">
                                        <input class="input" type="numeric" name="weight" value="<?php echo $player["weight"]; ?>" required />
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="control">
                                        <input class="input button is-link" type="submit" value="Updated Data" />
                                        <button type="button" class="button is-danger modal-toggle">Delete Player</button>
                                    </div>
                                </div>
                                <?php 
                                    if ( isset($success) ): ?>
                                        <div class="notification is-success">
                                            <?php
                                                if( $_POST["action"] == ACTION_DELETE ){
                                                    echo "Player successfully deleted";
                                                } else if( $_POST["action"] == ACTION_EDIT ){
                                                    echo "Player data updated successfully";
                                                }
                                            ?>
                                        </div>
                                <?php endif; ?>
                                <?php
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
        <div class="modal" id="modal-delete-warning">
            <div class="modal-background modal-toggle"></div>
            <div class="modal-content">
                <article class="message">
                    <div class="message-body">
                        <h2 class="title is-2">
                            Do you really want to delete this player from the database?
                        </h2>
                        <p class="paragraph">
                            This means that all partecipations in matches this player has played in will also
                            be deleted. <strong>This is irreversible!</strong>
                        </p>
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo ACTION_DELETE; ?>" />
                            <input type="hidden" name="id" value="<?php echo $player["id"]; ?>" />
                        
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