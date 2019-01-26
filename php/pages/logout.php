<?php
    require_once('config.php');
    if( !isset($_SESSION) ){
        session_start();
    }
    $_SESSION['logged'] = false;
    unset($_SESSION['name']);
    unset($_SESSION['role']);
?>
<!DOCTYPE html>
<html class="has-background-light full-height">
    <head>
        <title>Soccer Bets - Logout</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
    </head>
    <body class="has-background-light">
        <?php include_once(COMPONENTS . '/navbar.php'); ?>
        <div class="container">
            <h2 class="title is-2">
                You are now logged out
            </h2>
            <p class="paragraph">
                You can now proceed. If you need to access the control panel again you will need to login again,
            </p>
        </div>
    </body>
</html>