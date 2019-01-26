<?php require_once('config.php'); ?>
<!DOCTYPE html5>
<html>
    <head>
        <title>Soccer Bets</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
    </head>
    <body>
        <?php require_once(COMPONENTS . '/logincheck.php'); ?>
        <?php include(COMPONENTS . '/navbar.php'); ?>
        <div class="container">
            <?php
                if( $logged ){
                    echo "<h3 class='title is-3'>Ciao, " . $_SESSION['name'] . '</h3>';
                }
            ?>
        </div>
    </body>
</html>