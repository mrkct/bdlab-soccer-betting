<?php
    require_once('config.php');
    require_once(LIB . '/utils.php');
    require_once(COMPONENTS . '/logincheck.php');
    require_once(COMPONENTS . '/error_message.php');


    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }

    require_once(LIB . '/database.php');
    require_once(LIB . '/models/player.php');
    $db = db_connect();
    
    if( isset($_POST['name']) ){
        $name = $_POST['name'];
        $id = isset($_POST['id']) && !empty($_POST['id'])? $_POST['id'] : NULL;
        $bday = isset($_POST['birthday']) && !empty($_POST['birthday']) ? $_POST['birthday'] : NULL;
        $height = isset($_POST['height']) && !empty($_POST['height']) ? $_POST['height']: NULL;
        $weight = isset($_POST['weight']) && !empty($_POST['weight']) ? $_POST['weight'] : NULL;
        
        try{
            Player::prepare($db);
            Player::insert($db, $id, $name, $bday, $height, $weight);
            $success = true;
        }catch(PermissionDeniedException $e){
            $error = "You are not allowed to insert players' data";
        }catch(DuplicateDataException $e){
            $error = "There is already a player with that id";
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
                    <h2 class="title is-2 title-centered">Add Player</h2>
                    <form method="POST" class="controlpanel-form">
                        <div class="field">
                            <label class="label">Player's ID (optional)</label>
                            <div class="control">
                                <input class="input" name="id" type="numeric" />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Player's name</label>
                            <div class="control">
                                <input class="input" name="name" type="text" required />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Player's Birthday (optional)</label>
                            <div class="control">
                                <input class="input" name="birthday" type="date" required />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Player's Height (optional)</label>
                            <div class="control">
                                <input class="input" name="height" type="numeric" />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Player's Weight (optional)</label>
                            <div class="control">
                                <input class="input" name="weight" type="numeric" />
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
                                    New player successfully added
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