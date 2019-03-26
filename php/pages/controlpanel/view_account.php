<?php
    require_once('config.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/loggeduser.php');
    require_once(LIB . '/utils.php');
    require_once(COMPONENTS . '/logincheck.php');
    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }

    if( LoggedUser::getAffiliation() != NULL ){
        $db = db_connect();
        pg_prepare(
            $db, 
            'get_user', 
            'SELECT name AS betprovider_name
            FROM bet_provider 
            WHERE id = $1;'
        );
        $result = pg_execute($db, 'get_user', array(LoggedUser::getAffiliation()));
        $result = pg_fetch_assoc($result);
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
                    <h2 class="title is-2 title-centered">Your account</h2>
                    <div class="user-info notification is-primary">
                        <h3 class="title is-3"><?php echo LoggedUser::getName(); ?></h3>
                        <strong>User id:</strong>
                        <?php echo LoggedUser::getId(); ?><br>
                        <b>Role:</b>
                        <?php echo LoggedUser::getRole(); ?><br>
                        <b>Affiliated with:</b>
                        <?php
                            if( LoggedUser::getAffiliation() == NULL ){
                                echo "None";
                            } else {
                                if( $result["betprovider_name"] == NULL ){
                                    echo LoggedUser::getAffiliation();
                                } else {
                                    echo $result["betprovider_name"];
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .user-info{
                font-size: 20px;
            }
        </style>
    </body>
</html>