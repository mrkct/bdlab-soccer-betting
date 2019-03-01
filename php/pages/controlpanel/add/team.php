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
    if( isset($_POST['name']) && isset($_POST['shortname']) ){
        if( !isset($_POST['id']) ){
            pg_prepare(
                $db,
                'insert_team',
                'INSERT INTO team(longname, shortname) VALUES ($1, $2);'
            );
            $result = pg_execute($db, 'insert_team', array($_POST['name'], $_POST['shortname']));
        } else {
            pg_prepare(
                $db,
                'insert_team',
                'INSERT INTO team(id, longname, shortname) VALUES($1, $2, $3);',
            );
            $result = pg_execute($db, 'insert_team', array($_POST['id'], $_POST['name'], $_POST['shortname']));
        }
        if( $result != false ){
            $success = true;
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
                            <label class="label">Team's ID (optional)</label>
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
                            if ( $success ): ?>
                                <div class="notification is-success">
                                    New team successfully added
                                </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>