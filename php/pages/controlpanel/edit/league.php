<?php
    require_once('config.php');
    require_once(LIB .'/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/league.php');
    require_once(COMPONENTS . '/paginated-select.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/messages.php');


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
        League::prepare($db);
        $league = League::findById($db, $_GET["id"]);
        if( $league == null ){
            $error = "You might have followed a bad URL";
        }
    }

    if( isset($_POST["action"]) && isset($_POST["id"]) ){
        if( $_POST["action"] == ACTION_DELETE ){
            try{
                League::delete($db, $_POST["id"]);
                $success = true;
            }catch(PermissionDeniedException $e){
                $error = "You are not allowed to delete leagues data";
            }catch(DBException $e){
                $error = "An unknown error occurred[" . $e->getMessage() . "]";
            }
            
        } else if ( $_POST["action"] == ACTION_EDIT ){
            if( isset($_POST["id"]) && isset($_POST["name"]) && isset($_POST["country"]) ){
                try{
                    $league = League::edit(
                        $db,
                        $_POST["old_id"],
                        $_POST["id"],
                        $_POST["name"],
                        $_POST["country"]
                    );
                    $success = true;
                }catch(PermissionDeniedException $e){
                    $error = "You are not allowed to edit leagues data";
                }catch(DuplicateDataException $e){
                    $error = "There is already a league with that id";
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
                            <h2 class="title is-2 title-centered">Select the league to edit</h2> 
                            <?php 
                            $display_league = function($item){
                                return sprintf("%s(%s)", $item["name"], $item["country"]);
                            };
                            $link_league = function($item){
                                return sprintf("?id=%d", $item["id"]);
                            };

                            $total_leagues = pg_fetch_assoc(pg_query($db, "SELECT COUNT(*) AS leagues FROM league"))["leagues"];
                            create_paginated_select_form(
                                "SELECT * FROM league ORDER BY name, country LIMIT $1 OFFSET $2", 
                                $total_leagues, 
                                $display_league,
                                $link_league
                            );
                        }
                    ?>
                    <?php
                        if( $state == STATE_EDIT ): ?>
                            <h2 class="title is-2 title-centered">Edit League</h2>
                            <form method="POST" class="controlpanel-form">
                                <input type="hidden" name="action" value="<?php echo ACTION_EDIT; ?>" />
                                <input type="hidden" name="old_id" value="<?php echo $league["id"]; ?>" />

                                <div class="field">
                                    <label class="label">League's ID</label>
                                    <div class="control">
                                        <input class="input" name="id" type="numeric" value="<?php echo $league["id"]; ?>"/>
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Name of the team</label>
                                    <div class="control">
                                        <input class="input" name="name" value="<?php echo $league["name"]; ?>" required />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Country of the league</label>
                                    <div class="control">
                                        <input class="input" name="country" value="<?php echo $league["country"]; ?>" required />
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="control">
                                        <input class="input button is-link" type="submit" value="Insert data" />
                                        <button type="button" class="button is-danger modal-toggle">Delete League</button>
                                    </div>
                                </div>
                                <?php 
                                    if ( isset($success) ){
                                        if( $_POST["action"] == ACTION_DELETE ){
                                            create_message("League successfully deleted", MSG_SUCCESS);
                                        } else if( $_POST["action"] == ACTION_EDIT ){
                                            create_message("League data updated successfully", MSG_SUCCESS);
                                        }
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
        <div class="modal" id="modal-delete-warning">
            <div class="modal-background modal-toggle"></div>
            <div class="modal-content">
                <article class="message">
                    <div class="message-body">
                        <h2 class="title is-2">
                            Do you really want to delete this league from the database?
                        </h2>
                        <p class="paragraph">
                            This means that all matches played in this league, their quotes and their player
                            partecipations will also be deleted. <strong>This is irreversible!</strong>
                        </p>
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo ACTION_DELETE; ?>" />
                            <input type="hidden" name="id" value="<?php echo $league["id"]; ?>" />
                        
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