<?php
    require_once('config.php'); 
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/player.php');
    require_once(COMPONENTS . '/messages.php');

    
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
<!DOCTYPE html>
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
                    create_message($error, MSG_ERROR);
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
                    <h2 class="title is-2 title-centered">
                        Matches where he played recently
                    </h2>
                    <?php
                        pg_prepare(
                            $db, 
                            'recent_matches', 
                            'SELECT 
                                match.*,
                                home.shortname AS hometeam_shortname,
                                home.longname AS hometeam_longname, 
                                away.shortname AS awayteam_shortname, 
                                away.longname AS awayteam_longname
                             FROM played 
                             JOIN match ON match.id = played.match
                             JOIN team AS home ON home.id = match.hometeam
                             JOIN team AS away ON away.id = match.awayteam 
                             WHERE player=$1 ORDER BY played_on DESC LIMIT 10;'
                        );
                        $result = pg_execute($db, 'recent_matches', array($player['id']));
                    ?>
                    <table class="table is-striped is-bordered is-hoverable is-fullwidth">
                        <thead>
                            <th>Played on</th>
                            <th>Home Team</th>
                            <th>Away Team</th>
                            <th>Final Result</th>
                            <th>More Details</th>
                        </thead>
                        <tbody>
                            <?php
                                while($match = pg_fetch_assoc($result)):
                            ?>
                                <tr>
                                    <td><?php echo date("d/m/Y", strtotime($match["played_on"])); ?></td>
                                    <td><?php echo $match["hometeam_longname"]; ?></td>
                                    <td><?php echo $match["awayteam_longname"]; ?></td>
                                    <td><?php echo $match["hometeam_goals"], "-", $match["awayteam_goals"]; ?></td>
                                    <td>
                                        <a class="button is-small is-fullwidth" href="<?php echo sprintf("%s/view/match.php?id=%d", PAGES, $match["id"]);?>">View match</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
            <?php
                endif;
            ?>
        </div>
    </body>
</html>