<?php
    require_once('config.php');
    require_once(LIB . '/utils.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/match.php');
    require_once(LIB . '/models/team.php');
    require_once(LIB . '/models/league.php');
    require_once(COMPONENTS . '/error_message.php'); 

    if( isset($_GET["id"]) ){
        $db = db_connect();
        Match::prepare($db);
        Team::prepare($db);
        League::prepare($db);

        $match = Match::find($db, $_GET["id"]);
        if( $match == NULL ){
            $error = "There is no match with that id";
        } else {
            $hometeam = Team::find($db, $match["hometeam"]);
            $awayteam = Team::find($db, $match["awayteam"]);
            $league = League::findById($db, $match["league"]);
            $success = true;
        }
    } else {
        $error = "The URL is missing the match id. You might have followed a bad link or copied the link wrong.";
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
                if( isset($error) ){
                    show_message_on_error($error);
                }
            ?>
            <?php if( isset($success) ): ?>
            <div class="match-result-panel">
                <div class="match-info">
                    <div class="league-name">
                        <?php echo $league["name"]; ?> Season <?php echo $match["season"]; ?>
                    </div>
                    <div class="match-date">
                        <?php echo "Stage ", $match["stage"], ", ", format_date($match["played_on"]); ?>
                    </div>
                </div>
                <div class="match-teams columns">
                    <div class="match-team left column">
                        <?php echo $hometeam["longname"]; ?>
                    </div>
                    <div class="match-team column">
                        <div class="match-result-value">
                            <?php echo $match["hometeam_goals"]; ?>
                        </div>
                        <div class="match-team-vs-line"></div>
                        <div class="match-team-vs-circle">VS</div>
                        <div class="match-team-vs-line"></div>
                        <div class="match-result-value">
                            <?php echo $match["awayteam_goals"]; ?>
                        </div>
                    </div>
                    <div class="match-team right column">
                        <?php echo $awayteam["longname"]; ?>
                    </div>
                </div>
            </div>
            <div class="match-result-players">
                <?php
                    $hometeam_players = array();
                    $awayteam_players = array();
                    pg_prepare(
                        $db, 
                        'get_players', 
                        'SELECT played.team, player.*
                         FROM played 
                         JOIN player ON player.id = played.player 
                         WHERE played.match = $1;'
                    );
                    pg_prepare(
                        $db,
                        'get_mvps',
                        'SELECT id FROM get_match_mvp($1, $2);'
                    );
                    $result = pg_execute($db, 'get_players', array($match["id"]));
                    $players = pg_fetch_all($result);
                    $result = pg_execute($db, 'get_mvps', array($match["id"], $hometeam["id"]));
                    $hometeam_mvps = pg_fetch_all($result);
                    $result = pg_execute($db, 'get_mvps', array($match["id"], $awayteam["id"]));
                    $awayteam_mvps = pg_fetch_all($result);

                    for($i = 0; $i < sizeof($players); $i++){
                        if( $players[$i]["team"] == $hometeam["id"] ){
                            // Here we check if the player is in the list of mvps and set the property accordingly
                            $players[$i]["mvp"] = false;
                            for($j = 0; $j < sizeof($hometeam_mvps); $j++){
                                if( $hometeam_mvps[$j]["id"] == $players[$i]["id"] ){
                                    $players[$i]["mvp"] = true;
                                    break;
                                }
                            }
                            array_push($hometeam_players, $players[$i]);
                        } else {
                            // Same as above
                            $players[$i]["mvp"] = false;
                            for($j = 0; $j < sizeof($awayteam_mvps); $j++){
                                if( $awayteam_mvps[$j]["id"] == $players[$i]["id"] ){
                                    $players[$i]["mvp"] = true;
                                    break;
                                }
                            }
                            array_push($awayteam_players, $players[$i]);
                        }
                    }
                    pg_free_result($result);
                ?>
                <table class="table is-striped is-bordered is-hoverable is-fullwidth">
                    <?php
                        // Sometimes a team might have more players that have played than another
                        $until = max(sizeof($hometeam_players), sizeof($awayteam_players));
                        for($i = 0; $i < $until; $i++):
                            ?>
                            <tr>
                                <td>
                                    <?php
                                        $name = $i < sizeof($hometeam_players) ? $hometeam_players[$i]["name"]: "";
                                        echo $name;
                                        if( $i < sizeof($hometeam_players) && $hometeam_players[$i]["mvp"] ):
                                    ?>
                                        <div class="tag is-info" alt="Most Valuable Player in the team">MVP</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $name = $i < sizeof($awayteam_players) ? $awayteam_players[$i]["name"]: "";
                                        echo $name;
                                        if( $i < sizeof($awayteam_players) && $awayteam_players[$i]["mvp"] ):
                                    ?>
                                        <div class="tag is-info" alt="Most Valuable Player in the team">MVP</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                    <?php 
                        endfor;
                    ?>    
                </table>
            </div>
            <hr>
            <div class="match-result-quotes">
                <h2 class="title is-2 title-centered">Quotes for this match</h2>
                <?php
                    pg_prepare($db, 'get_quotes', 'SELECT * FROM quote WHERE match = $1;');
                    $result = pg_execute($db, 'get_quotes', array($match["id"]));
                ?>
                <?php
                    if( pg_num_rows($result) > 0 ): ?>
                        <table class="table is-striped is-bordered is-hoverable is-fullwidth">
                            <thead>
                                <th>Bet Provider</th>
                                <th>Home team(<?php echo $hometeam["shortname"]; ?>) wins</th>
                                <th>Match ends in draw</th>
                                <th>Away team(<?php echo $awayteam["shortname"]; ?>) wins</th>
                            </thead>
                            <tbody>
                                <?php
                                    while( $quote = pg_fetch_assoc($result) ):
                                        ?>
                                        <tr>
                                            <td><?php echo $quote["bet_provider"]; ?></td>
                                            <td><?php echo $quote["home_quote"]; ?></td>
                                            <td><?php echo $quote["draw_quote"]; ?></td>
                                            <td><?php echo $quote["away_quote"]; ?></td>
                                        </tr>
                                <?php 
                                    endwhile;
                                ?>
                            </tbody>
                        </table>
                <?php 
                    endif;
                    if( pg_num_rows($result) == 0 ): ?>
                        <h4 class="title is-4 title-centered">
                            There are no quotes available for this match
                        </h4>
                <?php
                    endif; ?>
            </div>
            <hr>
            <div class="match-result-stats">
                <h2 class="title is-2 title-centered">Players' stats for this game</h2>
                <?php
                    pg_prepare(
                        $db, 
                        'get_stats', 
                        'SELECT played.team, stats.*
                         FROM played
                         JOIN most_recent_stats($1) AS stats ON stats.player = played.player
                         WHERE played.match = $2'
                    );
                    $result = pg_execute($db, 'get_stats', array($match["played_on"], $match["id"]));
                    $stats = pg_fetch_all($result);
                    for($i = 0; $i < sizeof($stats); $i++){
                        $search_in = &$hometeam_players;
                        if( $stats[$i]["team"] == $awayteam["id"] ){
                            $search_in = &$awayteam_players;
                        }
                        for($j = 0; $j < sizeof($search_in); $j++){
                            if( $search_in[$j]["id"] == $stats[$i]["player"] ){
                                $search_in[$j]["stats"] = $stats[$i];
                                break;
                            }
                        }
                    }
                ?>
                <table class="table is-striped is-bordered is-hoverable">
                    <thead>
                        <th><abbr title="Player's Name">Player's Name</abbr></th>
                        <th><abbr title="Overall Rating">Overall Rating</abbr></th>
                        <th><abbr title="Potential">Potential</abbr></th>
                        <th><abbr title="Preferred Foot">Preferred Foot</abbr></th>
                        <th><abbr title="Attacking Work Rate">Attacking Work Rate</abbr></th>
                        <th><abbr title="Defensive Work Rate">Defensive Work Rate</abbr></th>
                        <th><abbr title="Crossing">Crossing</abbr></th>
                        <th><abbr title="Finishing">Finishing</abbr></th>
                        <th><abbr title="Heading Accuracy">Heading Accuracy</abbr></th>
                        <th><abbr title="Short Passing">Short Passing</abbr></th>
                        <th><abbr title="Volley">Volley</abbr></th>
                        <th><abbr title="Dribbling">Dribbling</abbr></th>
                        <th><abbr title="Curve">Curve</abbr></th>
                        <th><abbr title="Free Kick Accuracy">Free Kick Accuracy</abbr></th>
                        <th><abbr title="Long Passing">Long Passing</abbr></th>
                        <th><abbr title="Ball Control">Ball Control</abbr></th>
                        <th><abbr title="Acceleration">Acceleration</abbr></th>
                        <th><abbr title="Sprint Speed">Sprint Speed</abbr></th>
                        <th><abbr title="Agility">Agility</abbr></th>
                        <th><abbr title="Reactions">Reactions</abbr></th>
                        <th><abbr title="Balance">Balance</abbr></th>
                        <th><abbr title="Shot Power">Shot Power</abbr></th>
                        <th><abbr title="Jumping">Jumping</abbr></th>
                        <th><abbr title="Stamina">Stamina</abbr></th>
                        <th><abbr title="Strength">Strength</abbr></th>
                        <th><abbr title="Long Shots">Long Shots</abbr></th>
                        <th><abbr title="Aggression">Aggression</abbr></th>
                        <th><abbr title="Interceptions">Interceptions</abbr></th>
                        <th><abbr title="Positioning">Positioning</abbr></th>
                        <th><abbr title="Vision">Vision</abbr></th>
                        <th><abbr title="Penalties">Penalties</abbr></th>
                        <th><abbr title="Marking">Marking</abbr></th>
                        <th><abbr title="Standing Tackle">Standing Tackle</abbr></th>
                        <th><abbr title="Sliding Tackle">Sliding Tackle</abbr></th>
                        <th><abbr title="Diving">Diving</abbr></th>
                        <th><abbr title="Handling">Handling</abbr></th>
                        <th><abbr title="Kicking">Kicking</abbr></th>
                        <th><abbr title="Positioning">Positioning</abbr></th>
                        <th><abbr title="Reflexes">Reflexes</abbr></th>
                    </thead>
                    <tbody>
                        <?php foreach($hometeam_players as $player):?>
                            <tr class="hometeam-row"><?php create_stats_row($player); ?></tr>
                        <?php endforeach; ?>
                        <?php foreach($awayteam_players as $player):?>
                            <tr class="awayteam-row"><?php create_stats_row($player); ?></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php
            function create_stats_row($player){
                ?>
                    <td><?php echo $player["name"]; ?></td>
                    <td><?php echo $player["stats"]["overall_rating"]; ?></td>
                    <td><?php echo $player["stats"]["potential"]; ?></td>
                    <td><?php echo $player["stats"]["preferred_foot"]; ?></td>
                    <td><?php echo $player["stats"]["attacking_work_rate"]; ?></td>
                    <td><?php echo $player["stats"]["defensive_work_rate"]; ?></td>
                    <td><?php echo $player["stats"]["crossing"]; ?></td>
                    <td><?php echo $player["stats"]["finishing"]; ?></td>
                    <td><?php echo $player["stats"]["heading_accuracy"]; ?></td>
                    <td><?php echo $player["stats"]["short_passing"]; ?></td>
                    <td><?php echo $player["stats"]["volleys"]; ?></td>
                    <td><?php echo $player["stats"]["dribbling"]; ?></td>
                    <td><?php echo $player["stats"]["curve"]; ?></td>
                    <td><?php echo $player["stats"]["free_kick_accuracy"]; ?></td>
                    <td><?php echo $player["stats"]["long_passing"]; ?></td>
                    <td><?php echo $player["stats"]["ball_control"]; ?></td>
                    <td><?php echo $player["stats"]["acceleration"]; ?></td>
                    <td><?php echo $player["stats"]["sprint_speed"]; ?></td>
                    <td><?php echo $player["stats"]["agility"]; ?></td>
                    <td><?php echo $player["stats"]["reactions"]; ?></td>
                    <td><?php echo $player["stats"]["balance"]; ?></td>
                    <td><?php echo $player["stats"]["shot_power"]; ?></td>
                    <td><?php echo $player["stats"]["jumping"]; ?></td>
                    <td><?php echo $player["stats"]["stamina"]; ?></td>
                    <td><?php echo $player["stats"]["strength"]; ?></td>
                    <td><?php echo $player["stats"]["long_shots"]; ?></td>
                    <td><?php echo $player["stats"]["aggression"]; ?></td>
                    <td><?php echo $player["stats"]["interceptions"]; ?></td>
                    <td><?php echo $player["stats"]["positioning"]; ?></td>
                    <td><?php echo $player["stats"]["vision"]; ?></td>
                    <td><?php echo $player["stats"]["penalties"]; ?></td>
                    <td><?php echo $player["stats"]["marking"]; ?></td>
                    <td><?php echo $player["stats"]["standing_tackle"]; ?></td>
                    <td><?php echo $player["stats"]["sliding_tackle"]; ?></td>
                    <td><?php echo $player["stats"]["gk_diving"]; ?></td>
                    <td><?php echo $player["stats"]["gk_handling"]; ?></td>
                    <td><?php echo $player["stats"]["gk_kicking"]; ?></td>
                    <td><?php echo $player["stats"]["gk_positioning"]; ?></td>
                    <td><?php echo $player["stats"]["gk_reflexes"]; ?></td>
        <?php
            }
        ?>
    </body>
</html>