<?php
    require_once('config.php');
    require_once(COMPONENTS . '/logincheck.php');
    if( !$logged ){
        header('location: ' . PAGES . '/login.php');
        exit();
    }

    require_once(LIB . '/database.php');
    $db = db_connect();
    pg_prepare(
        $db, 
        'get_user', 
        'SELECT 
            collaborator.id AS collaborator_id, 
            collaborator.name AS collaborator_name, 
            collaborator.role AS collaborator_role, 
            bet_provider.id AS bet_provider_id,
            bet_provider.name AS bet_provider_name
        FROM collaborator 
        LEFT JOIN bet_provider ON bet_provider.id = collaborator.affiliation
        WHERE collaborator.id = $1;'
    );
    $result = pg_execute($db, 'get_user', array($_SESSION['id']));
    $result = pg_fetch_assoc($result);
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
                    <h2 class="title is-2 title-centered">Your account</h2>
                    <div class="user-info">
                        <b>User id:</b>
                        <?php echo $result["collaborator_id"]; ?><br>
                        <b>Name:</b>
                        <?php echo $result["collaborator_name"]; ?><br>
                        <b>Role:</b>
                        <?php echo $result["collaborator_role"]; ?><br>
                        <b>Affiliated with:</b>
                        <?php
                            echo $result["bet_provider_name"]? $result["bet_provider_name"] : "None";
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .user-info{
                font-size: 24px;
            }
        </style>
    </body>
</html>