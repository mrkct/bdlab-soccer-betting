<?php
    require_once('config.php');
    require_once(LIB .'/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/match.php');
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

    $can_edit = true;
    $state = STATE_SELECT;
    $db = db_connect();
    if( isset($_GET["id"]) ){
        $state = STATE_EDIT;
        Match::prepare($db);
        $match = Match::find($db, $_GET["id"]);
        if( $match == null ){
            $match = "You might have followed a bad URL";
        } else {
            if( $match["created_by"] != LoggedUser::getId() ){
                $can_edit = false;
            }
        }
    }

    if( are_set(["action", "id"], $_POST) ){
        Match::prepare($db);
        if( $_POST["action"] == ACTION_DELETE ){
            try{
                Match::delete($db, $_POST["id"]);
                $success = true;
                $deleted = true;
            }catch(PermissionDeniedException $e){
                $error = "You are not allowed to delete this match data";
            }catch(DBException $e){
                $error = "An unknown error occurred[" . $e->getMessage() . "]";
            }
            
        } else if ( $_POST["action"] == ACTION_EDIT ){
            if( are_set(["hometeam_goals", "awayteam_goals", "season", "stage"], $_POST) ) {
                try{
                    $player = Match::edit(
                        $db, 
                        $_POST["id"],
                        $match["league"],
                        $_POST["season"],
                        $_POST["stage"],
                        $_POST["played_on"],
                        $match["hometeam"],
                        $match["awayteam"],
                        $_POST["hometeam_goals"],
                        $_POST["awayteam_goals"]
                    );
                    $success = true;
                }catch(PermissionDeniedException $e){
                    $error = "You are not allowed to edit players data";
                }catch(DuplicateDataException $e){
                    $error = "There is already a player with that id";
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
                            <h2 class="title is-2 title-centered">Select the match to edit</h2> 
                            <?php 
                            $display_match = function($item){
                                return sprintf(
                                    "%s %s stage %d: %s - %s", 
                                    $item["season"], 
                                    $item["league_name"], 
                                    $item["stage"], 
                                    $item["hometeam_shortname"], 
                                    $item["awayteam_shortname"]
                                );
                            };
                            $link_match = function($item){
                                return "?id=" . $item["id"];
                            };

                            pg_prepare($db, "total_matches", "SELECT COUNT(*) AS matches FROM match WHERE created_by = $1;");
                            $total_matches = pg_fetch_assoc(
                                pg_execute($db, "total_matches", array(LoggedUser::getId()))
                            )["matches"];
                            create_paginated_select_form(
                                "SELECT 
                                    match.*, 
                                    league.name AS league_name,
                                    H.shortname AS hometeam_shortname,
                                    A.shortname AS awayteam_shortname
                                 FROM match 
                                 JOIN league ON league.id = match.league
                                 JOIN team AS H ON H.id = match.hometeam
                                 JOIN team AS A ON A.id = match.awayteam
                                 WHERE match.created_by = $1
                                 ORDER BY league_name, match.season, match.stage, match.played_on 
                                 LIMIT $2 OFFSET $3", 
                                $total_matches, 
                                $display_match,
                                $link_match,
                                array(LoggedUser::getId())
                            );
                        } 
                    ?>
                    <?php
                        if( $state == STATE_EDIT ): ?>
                            <h2 class="title is-2 title-centered">Edit Match</h2>
                            <form method="POST" class="controlpanel-form">
                                <input type="hidden" name="action" value="<?php echo ACTION_EDIT; ?>" />
                                <input type="hidden" name="id" value="<?php echo $match["id"]; ?>" />
                                
                                <div class="columns">
                                    <div class="column">
                                        <div class="field">
                                            <label class="label">Home team Goals</label>
                                            <div class="control">
                                                <input 
                                                    class="input" 
                                                    type="numeric" 
                                                    name="hometeam_goals" 
                                                    value="<?php echo $match["hometeam_goals"]; ?>" />
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label class="label">Season</label>
                                            <div class="control">
                                                <input 
                                                    class="input" 
                                                    type="text" 
                                                    name="season" 
                                                    value="<?php echo $match["season"]; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="column">
                                        <div class="field">
                                            <label class="label">Away team Goals</label>
                                            <div class="control">
                                                <input 
                                                    class="input" 
                                                    type="numeric" 
                                                    name="awayteam_goals" 
                                                    value="<?php echo $match["awayteam_goals"]; ?>" />
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label class="label">Stage</label>
                                            <div class="control">
                                                <input 
                                                    class="input" 
                                                    type="text" 
                                                    name="stage" 
                                                    value="<?php echo $match["stage"]; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Played On</label>
                                    <div class="control">
                                        <input 
                                            class="input" 
                                            type="date" 
                                            name="played_on" 
                                            value="<?php echo $match["played_on"]; ?>" />
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="control">
                                        <?php
                                            if( $can_edit && (!isset($success) || !isset($deleted)) ): ?>
                                                <input class="input button is-link" type="submit" value="Update Data" />
                                                <button type="button" class="button is-danger modal-toggle delete-button">
                                                    Delete Match
                                                </button>
                                                <a 
                                                    class="button is-info restart-button" 
                                                    href="../add/match_players.php?id=<?php echo $match["id"]; ?>" >
                                                    Edit player partecipations
                                                </a>
                                        <?php
                                            endif; ?>
                                        <?php
                                            if( isset($success) ): ?>
                                                <a class="button is-primary restart-button" href="?">
                                                    Edit another match
                                                </a>
                                        <?php
                                            endif; ?>
                                        <?php
                                            if( !$can_edit ){
                                                create_message(
                                                    "<strong>Warning: </strong> You are not allowed to edit this match data. " + 
                                                    "Only the user who added this match to the database can edit it", 
                                                    MSG_WARNING);
                                            }
                                        ?>
                                    </div>
                                </div>
                                <?php 
                                    if ( isset($success) ){
                                        if( $_POST["action"] == ACTION_DELETE ){
                                            create_message("Match successfully deleted", MSG_SUCCESS);
                                        } else if( $_POST["action"] == ACTION_EDIT ){
                                            create_message("Match data updated successfully", MSG_SUCCESS);
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
                            Do you really want to delete this match from the database?
                        </h2>
                        <p class="paragraph">
                            This means that all quotes for this match and players' partecipations in this match will also
                            be deleted. <strong>This is irreversible!</strong>
                        </p>
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo ACTION_DELETE; ?>" />
                            <input type="hidden" name="id" value="<?php echo $match["id"]; ?>" />
                        
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