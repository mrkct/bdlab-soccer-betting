<?php
    define('ERR_MISSING_ID', 'MISSING ID IN URL');
    define('ERR_UNKNOWN_TEAM', 'UNKNOWN TEAM');
    require_once('config.php'); 

    $error = null;

    if( isset($_GET["id"]) ){
        require_once(LIB . '/database.php');
        require_once(LIB . '/models/team.php');

        $db = db_connect();
        Team::prepare($db);

        $team = Team::find($db, $_GET["id"]);
        if( $team == NULL ){
            $error = ERR_UNKNOWN_TEAM;
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
        <link rel="stylesheet" href="<?php echo CSS; ?>/view-match.css">
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
                <h1 class="title is-1">
                    <?php echo $team["longname"]; ?>(<?php echo $team["shortname"]; ?>)
                </h1>
                <?php
                    pg_prepare(
                        $db, 
                        'get_matches',
                        'SELECT 
                            match.*, 
                            home.shortname AS hometeam_shortname,
                            home.longname AS hometeam_longname, 
                            away.shortname AS awayteam_shortname, 
                            away.longname AS awayteam_longname
                         FROM match 
                         JOIN team AS home ON home.id = hometeam
                         JOIN team AS away ON away.id = awayteam
                         WHERE hometeam = $1 OR awayteam = $1 
                         ORDER BY played_on 
                         DESC LIMIT 10'
                    );
                    $result = pg_execute($db, 'get_matches', array($team["id"]));
                ?>
                <h2 class="title is-2 title-centered">
                    Latest matches
                </h2>
                <table class="table is-striped is-bordered is-hoverable is-fullwidth">
                    <thead>
                        <th>Played on</th>
                        <th>Home Team</th>
                        <th>Away Team</th>
                        <th>Final Result</th>
                    </thead>
                    <tbody>
                        <?php
                            while($match = pg_fetch_assoc($result)):
                        ?>
                            <a href="/bdlab/php/pages/view/match.php?id=<?php echo $match["id"]; ?>">
                            <tr>
                                <td><?php echo date("d/m/Y", strtotime($match["played_on"])); ?></td>
                                <td><?php echo $match["hometeam_longname"]; ?></td>
                                <td><?php echo $match["awayteam_longname"]; ?></td>
                                <td><?php echo $match["hometeam_goals"], "-", $match["awayteam_goals"]; ?></td>
                            </tr>
                            </a>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php
                endif;
            ?>
        </div>
    </body>
</html>