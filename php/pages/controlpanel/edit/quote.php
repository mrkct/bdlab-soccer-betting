<?php
    require_once('config.php');
    require_once(LIB .'/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/quote.php');
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
    if( isset($_GET["match"]) && isset($_GET["bp"]) ){
        $state = STATE_EDIT;
        Quote::prepare($db);
        $quote = Quote::find($db, $_GET["match"], $_GET["bp"]);
        if( $quote == null ){
            $error = "You might have followed a bad URL";
        }
    }

    if( isset($_POST["action"]) && isset($_POST["match"]) && isset($_POST["bp"]) ){
        Quote::prepare($db);
        if( $_POST["action"] == ACTION_DELETE ){
            try{
                Quote::delete(
                    $db, 
                    $_POST["match"], 
                    $_POST["bp"]
                );
                $success = true;
                $deleted = true;
            }catch(PermissionDeniedException $e){
                $error = "You are not allowed to delete this quote";
            }catch(DBException $e){
                $error = "An unknown error occurred[" . $e->getMessage() . "]";
            }
        } else if ( $_POST["action"] == ACTION_EDIT ){
            if( isset($_POST["home_quote"]) && isset($_POST["draw_quote"]) && isset($_POST["away_quote"]) ){
                try{
                    $quote = Quote::edit(
                        $db, 
                        $_POST["match"],
                        $_POST["bp"],
                        $_POST["home_quote"],
                        $_POST["draw_quote"],
                        $_POST["away_quote"]
                    );
                    $success = true;
                }catch(PermissionDeniedException $e){
                    $error = "You are not allowed to edit this quote";
                }catch(DuplicateDataException $e){
                    $error = "There is already a quote for that match and that bet provider";
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
                            <h2 class="title is-2 title-centered">Select the quote to edit</h2> 
                            <?php 
                            $display_quote = function($item){
                                return sprintf(
                                    "%s vs %s - %s", 
                                    $item["hometeam_shortname"], 
                                    $item["awayteam_shortname"], 
                                    $item["played_on"]
                                );
                            };
                            $link_quote = function($item){
                                return sprintf("?match=%s&bp=%s", $item["match"], $item["bet_provider"]);
                            };
                            pg_prepare($db, "total_quotes", "SELECT COUNT(*) AS quotes FROM quote WHERE created_by=$1;");
                            $total_quotes = pg_fetch_assoc(
                                pg_execute($db, "total_quotes", array(LoggedUser::getId()))
                            )["quotes"];
                            create_paginated_select_form(
                                "SELECT 
                                    quote.*, 
                                    H.shortname AS hometeam_shortname, 
                                    A.shortname AS awayteam_shortname,
                                    M.played_on AS played_on
                                 FROM quote 
                                 JOIN match AS M ON M.id = quote.match 
                                 JOIN team AS H ON H.id = M.hometeam 
                                 JOIN team AS A ON A.id = M.awayteam
                                 WHERE quote.created_by = $1
                                 ORDER BY M.played_on DESC
                                 LIMIT $2 OFFSET $3;", 
                                $total_quotes, 
                                $display_quote,
                                $link_quote,
                                array(LoggedUser::getId())
                            );
                        } 
                    ?>
                    <?php
                        if( isset($quote) && $quote != NULL && $state == STATE_EDIT ): ?>
                            <h2 class="title is-2 title-centered">Edit Quote</h2>
                            <form method="POST" class="controlpanel-form">
                                <input type="hidden" name="action" value="<?php echo ACTION_EDIT; ?>" />
                                <input type="hidden" name="match" value="<?php echo $quote["match"]; ?>" />
                                <input type="hidden" name="bp" value="<?php echo $quote["bet_provider"]; ?>" />

                                <div class="field">
                                    <label class="label">Home team wins quote</label>
                                    <div class="control">
                                        <input 
                                            class="input" 
                                            type="numeric" 
                                            name="home_quote" 
                                            min=0 
                                            value="<?php echo $quote["home_quote"]; ?>" />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Match draw quote</label>
                                    <div class="control">
                                        <input 
                                            class="input" 
                                            type="numeric" 
                                            name="draw_quote" 
                                            min=0 
                                            value="<?php echo $quote["draw_quote"]; ?>" />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Away team wins quote</label>
                                    <div class="control">
                                        <input 
                                            class="input" 
                                            type="numeric" 
                                            name="away_quote" 
                                            min=0 
                                            value="<?php echo $quote["away_quote"]; ?>" />
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="control">
                                        <?php
                                            if( $quote["created_by"] == LoggedUser::getId() && !isset($success) || !isset($deleted) ): ?>
                                                <input class="input button is-link" type="submit" value="Updated Data" />
                                                <button type="button" class="button is-danger modal-toggle delete-button">
                                                    Delete Quote
                                                </button>
                                        <?php
                                            endif; ?>
                                        <?php
                                            if( isset($success) ): ?>
                                                <a class="button is-primary restart-button" href="?">
                                                    Edit another quote
                                                </a>
                                        <?php
                                            endif; ?>
                                    </div>
                                </div>
                                <?php
                                    if( $quote != null && $quote["created_by"] != LoggedUser::getId() ){
                                        create_message(
                                            "<strong>Warning: </strong>You cannot modify this quote as you are not the one who added it to the database", 
                                            MSG_WARNING
                                        );
                                    }
                                    if ( isset($success) ){
                                        if( $_POST["action"] == ACTION_DELETE ){
                                            create_message("Quote successfully deleted", MSG_SUCCESS);
                                        } else if( $_POST["action"] == ACTION_EDIT ){
                                            create_message("Quote data updated successfully", MSG_SUCCESS);
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
                            Do you really want to delete this quote from the database?
                        </h2>
                        <p class="paragraph">
                            This means that all partecipations in matches this quote has played in will also
                            be deleted. <strong>This is irreversible!</strong>
                        </p>
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo ACTION_DELETE; ?>" />
                            <input type="hidden" name="match" value="<?php echo $quote["match"]; ?>" />
                            <input type="hidden" name="bp" value="<?php echo $quote["bet_provider"]; ?>" />
                        
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