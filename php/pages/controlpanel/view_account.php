<?php
    require_once('config.php');
    require_once(COMPONENTS . '/logincheck.php');
    if( !$logged ){
        header('location: ' . PAGES . '/login.php');
        exit();
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
                <div class="container column is-three-quarters">
                    <h2 class="title is-2">Your account</h2>
                    <?php
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
                    <h3 class="title is-3">User id: <?php echo $result['collaborator_id']; ?></h3>
                    <h3 class="title is-3">Name: <?php echo $result['collaborator_name']; ?></h3>
                    <h3 class="title is-3">Role: <?php echo $result['collaborator_role']; ?></h3>
                    <?php
                        if( $result['bet_provider_name'] ):
                            ?>
                            <h3 class="title is-3">Affiliated with: <?php echo $result['bet_provider_name']; ?></h3>
                        <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>