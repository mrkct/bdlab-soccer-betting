<?php
    require_once('config.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/error_message.php');


    if( !$logged ){
        header('location: /bdlab/php/login.php');
        exit();
    }

    require_once(LIB . '/database.php');
    require_once(LIB . '/models/team.php');
    $db = db_connect();
    if( isset($_POST['name']) && isset($_POST['shortname']) ){
        try{
            Team::prepare($db);
            Team::insert($db, $_POST['id'], $_POST['name'], $_POST['shortname']);
            $success = true;
        }catch(PermissionDeniedException $e){
            $error = "You are not allowed to insert team's data";
        }catch(DuplicateDataException $e){
            $error = "There is already a team with that id";
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
                    <h2 class="title is-2 title-centered">Add Team</h2>
                    <form method="POST" class="controlpanel-form">
                        <div class="field">
                            <label class="label">Team's ID</label>
                            <div class="control">
                                <input class="input" name="id" type="numeric" />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Name of the team</label>
                            <div class="control">
                                <input class="input" name="name" required />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Short name for the team</label>
                            <div class="control">
                                <input class="input" name="shortname" required />
                            </div>
                        </div>
                        <div class="field">
                            <div class="control">
                                <input class="input button is-link" type="submit" value="Insert data" />
                            </div>
                        </div>
                        <?php 
                            if ( isset($success) ): ?>
                                <div class="notification is-success">
                                    New team successfully added
                                </div>
                        <?php endif; ?>
                        <?php
                            if( isset($error) ){
                                show_message_on_error($error);
                            }
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>