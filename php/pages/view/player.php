<?php
    define('ERR_MISSING_ID', 'MISSING ID IN URL');
    define('ERR_UNKNOWN_PLAYER', 'UNKNOWN PLAYER');
    require_once('config.php'); 

    $error = null;

    if( isset($_GET["id"]) ){
        require_once(LIB . '/database.php');
        require_once(LIB . '/models/player.php');

        $db = db_connect();
        Player::prepare($db);

        $player = Player::find($db, $_GET["id"]);
        if( $player == NULL ){
            $error = ERR_UNKNOWN_PLAYER;
        }
    } else {
        $error = ERR_MISSING_ID;
    }
?>
<!DOCTYPE html5>
<html>
    <head>
        <title>Soccer Bets</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
        <link rel="stylesheet" href="<?php echo CSS; ?>/player-card.css">
    </head>
    <body>
        <?php require_once(COMPONENTS . '/logincheck.php'); ?>
        <?php include(COMPONENTS . '/navbar.php'); ?>
        <div class="container">
            <?php
                if( $error ):
                    ?>
                    <div class="notification is-danger">
                        There was an error showing this page: <?php echo $error; ?>
                    </div>
                    <?php
                endif;
            ?>
            <?php
                if( !$error ):
                    ?>
                    <div class="player-card">
                        <div class="player-image">
                            <figure class="image is-4by3">
                                <img src="https://bulma.io/images/placeholders/1280x960.png" alt="Missing image" />
                            </figure>
                        </div>
                        <div class="player-info">
                            <h3 class="title is-3">
                                <?php echo $player["name"]; ?>
                            </h3>
                            <p><b>Height: </b><?php echo $player["height"]; ?></p>
                            <p><b>Weight: </b><?php echo $player["weight"]; ?></p>
                            <p><b>Birthday: </b><?php echo date('d/m/y', strtotime($player["birthday"])); ?></p>
                        </div>
                    </div>
            <?php
                endif;
            ?>
        </div>
    </body>
</html>