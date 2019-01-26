<?php
    require_once('config.php');
    require_once(COMPONENTS . '/logincheck.php');
    if( !$logged ){
        header('location: /bdlab/php/login.php');
        exit();
    }

    require_once(LIB . '/database.php');
    $db = db_connect();
    $success = false;
    if( isset($_POST['name']) ){
        $name = $_POST['name'];
        $id = isset($_POST['id']) && !empty($_POST['id'])? $_POST['id'] : NULL;
        $bday = isset($_POST['birthday']) && !empty($_POST['birthday']) ? $_POST['birthday'] : NULL;
        $height = isset($_POST['height']) && !empty($_POST['height']) ? $_POST['height']: NULL;
        $weight = isset($_POST['weight']) && !empty($_POST['weigth']) ? $_POST['weight'] : NULL;
        
        if( $id == NULL ){
            pg_prepare(
                $db,
                'insert_player',
                'INSERT INTO player(name, birthday, height, weigth) VALUES ($1, $2, $3, $4);'
            );
            $result = pg_execute($db, 'insert_player', array($name, $bday, $height, $weight));
            $success = true;
        } else {
            pg_prepare(
                $db,
                'check_player',
                'SELECT * FROM player WHERE id = $1'
            );
            $result1 = pg_execute($db, 'check_player', array($id));
            if( pg_num_rows($result1) == 0 ){
                pg_prepare(
                    $db,
                    'insert_player',
                    'INSERT INTO player(id, name, birthday, height, weigth) VALUES ($1, $2, $3, $4, $5);'
                );
                $result = pg_execute($db, 'insert_player', array($id, $name, $bday, $height, $weight));
                $success = true;
            } else {
                $success = false;
                $error = "There is already a player with that id";
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
                                <input class="input" name="name" type="text" />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Player's Birthday (optional)</label>
                            <div class="control">
                                <input class="input" name="birthday" type="date" />
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
                                <input class="input" name="weigth" type="numeric" />
                            </div>
                        </div>
                        <div class="field">
                            <div class="control">
                                <input class="input button is-link" type="submit" value="Insert data" />
                            </div>
                        </div>
                        <?php 
                            if ( $success ): ?>
                                <div class="notification is-success">
                                    New player successfully added
                                </div>
                        <?php endif; ?>
                        <?php
                            if( !$success && isset($error) ): ?>
                                <div class="notification is-danger">
                                    Error: <?php echo $error; ?>
                                </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>