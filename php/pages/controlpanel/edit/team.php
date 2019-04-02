<?php
    require_once('config.php');
    require_once(LIB .'/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/team.php');
    require_once(COMPONENTS . '/paginated-select.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/error_message.php');


    define('STATE_SELECT_TEAM', 'select_team');
    define('STATE_EDIT_TEAM', 'edit_team');
    define('ACTION_DELETE', 'delete');
    define('ACTION_EDIT', 'edit');

    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }
        
    $state = STATE_SELECT_TEAM;
    $db = db_connect();
    if( isset($_GET["id"]) ){
        $state = STATE_EDIT_TEAM;
        Team::prepare($db);
        $team = Team::find($db, $_GET["id"]);
        if( $team == null ){
            $error = "You might have followed a bad URL";
        }
    }

    if( isset($_POST["action"]) && isset($_POST["id"]) ){
        if( $_POST["action"] == ACTION_DELETE ){
            try{
                Team::delete($db, $_POST["id"]);
                $success = true;
            }catch(PermissionDeniedException $e){
                $error = "You are not allowed to delete teams data";
            }catch(DBException $e){
                $error = "An unknown error occurred[" . $e->getMessage() . "]";
            }
            
        } else if ( $_POST["action"] == ACTION_EDIT ){
            if( isset($_POST["longname"]) && isset($_POST["shortname"]) ){
                try{
                    // TODO: Edit the team data
                    // Team::edit($db, ...)
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
                        if( $state == STATE_SELECT_TEAM ){
                            ?> 
                            <h2 class="title is-2 title-centered">Select the team to edit</h2> 
                            <?php 
                            $display_team = function($item){
                                return $item["longname"];
                            };
                            $link_team = function($item){
                                return "?id=" . $item["id"];
                            };

                            $total_teams = pg_fetch_assoc(pg_query($db, "SELECT COUNT(*) AS teams FROM team"))["teams"];
                            create_paginated_select_form(
                                "SELECT * FROM team ORDER BY longname LIMIT $1 OFFSET $2", 
                                $total_teams, 
                                $display_team,
                                $link_team
                            );
                        } 
                    ?>
                    <?php
                        if( $state == STATE_EDIT_TEAM ): ?>
                            <h2 class="title is-2 title-centered">Edit Team</h2>
                            <form method="POST" class="controlpanel-form">
                                <input type="hidden" name="action" value="<?php echo ACTION_EDIT; ?>" />
                                <div class="field">
                                    <label class="label">Team's ID</label>
                                    <div class="control">
                                        <input class="input" name="id" type="numeric" value="<?php echo $team["id"]; ?>"/>
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Name of the team</label>
                                    <div class="control">
                                        <input class="input" name="longname" value="<?php echo $team["longname"]; ?>" required />
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Short name for the team</label>
                                    <div class="control">
                                        <input class="input" name="shortname" value="<?php echo $team["shortname"]; ?>" required />
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="control">
                                        <input class="input button is-link" type="submit" value="Insert data" />
                                        <button type="button" class="button is-danger modal-toggle">Delete Match</button>
                                    </div>
                                </div>
                                <?php 
                                    if ( isset($success) ): ?>
                                        <div class="notification is-success">
                                            <?php
                                                if( $_POST["action"] == ACTION_DELETE ){
                                                    echo "Team successfully deleted";
                                                } else if( $_POST["action"] == ACTION_EDIT ){
                                                    echo "Team data updated successfully";
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
                            Do you really want to delete this team from the database?
                        </h2>
                        <p class="paragraph">
                            This means that all matches this team has played and their related quotes
                            will also be deleted. This is irreversible!
                        </p>
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo ACTION_DELETE; ?>" />
                            <input type="hidden" name="id" value="<?php echo $team["id"]; ?>" />
                        
                            <input type="submit" class="button is-danger" value="Yes, delete it" />
                        </form>
                    </div>
                </article>
            </div>
            <button class="modal-close is-large modal-toggle" aria-label="close"></button>
        </div>
        <script type="text/javascript">
            var toggleElements = document.getElementsByClassName("modal-toggle");
            for(var i = 0; i < toggleElements.length; i++){
                toggleElements[i].addEventListener("click", toggle_modal)
            }
            
            function toggle_modal(){
                let modalClasses = document.getElementById("modal-delete-warning").classList;
                if( !modalClasses.contains("is-active") ){
                    modalClasses.add("is-active");
                } else {
                    modalClasses.remove("is-active");
                }
            }
        </script>
    </body>
</html>