<?php
    require_once('config.php'); 
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/player.php');
    require_once(COMPONENTS . '/error_message.php');

    
    if( isset($_GET["id"]) ){
        $db = db_connect();
        Player::prepare($db);

        $player = Player::find($db, $_GET["id"]);
        if( $player == NULL ){
            $error = "There is no player with that id";
        } else {
            $success = true;
        }
    } else {
        $error = "The URL is missing the player id. You might have followed a bad link or copied the link wrong";
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
                if( isset($error) ){
                    show_message_on_error($error);
                }
            ?>
            <?php
                if( isset($success) ):
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
                            <p><b>Height: </b><?php echo $player["height"]; ?>cm</p>
                            <p><b>Weight: </b><?php echo $player["weight"]; ?>lb</p>
                            <p><b>Birthday: </b><?php echo date('d/m/y', strtotime($player["birthday"])); ?></p>
                        </div>
                    </div>
            <?php
                endif;
            ?>
        </div>
    </body>
</html>