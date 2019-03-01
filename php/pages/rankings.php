<?php require_once('config.php'); ?>
<?php
    require_once(LIB . '/database.php');
    require_once(LIB . '/models/league.php');
    $db = db_connect();
    
    $league_id = isset($_GET["league"])? $_GET["league"] : 1187;
    $season = isset($_GET["season"])? $_GET["season"] : "2008/2009";
    
    League::prepare($db);
    $league = League::findById($db, $league_id);
    
    pg_prepare(
        $db,
        "get_top10", 
        "SELECT R.*, team.longname as longname
         FROM rankings AS R
         JOIN team ON team.id = R.team
         WHERE season = $1 AND league = $2 
         ORDER BY won_games 
         LIMIT 10;"
    );
    $result = pg_execute(
        $db, 
        "get_top10", 
        array(
            $season, 
            $league["id"]
        )
    );
?>
<!DOCTYPE html5>
<html>
    <head>
        <title>Soccer Bets - Rankings</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
    </head>
    <body>
        <?php require_once(COMPONENTS . '/logincheck.php'); ?>
        <?php include(COMPONENTS . '/navbar.php'); ?>
        <div class="container">
            <h1 class="title is-1 title-centered"><?php echo $league["name"]; ?></h1>
            <h3 class="title is-3 title-centered"><?php echo "Season ", $season; ?></h3>
            <form method="GET" class="select-container">
                <div class="field">
                    <label class="label">League</label>
                    <div class="control">
                        <div class="select">
                            <select name="league" required>
                                <?php
                                    pg_prepare($db, "get_leagues", "SELECT * FROM league ORDER BY name;");
                                    $league_result = pg_execute($db, "get_leagues", array());
                                    while($league = pg_fetch_assoc($league_result) ):
                                        ?>
                                        <option value="<?php echo $league["id"]; ?>">
                                            <?php echo $league["name"]; ?>
                                        </option>
                                <?php 
                                    endwhile; 
                                    pg_free_result($league_result); 
                                ?>    
                            </select>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Season</label>
                    <div class="control">
                        <div class="select">
                            <select name="season" required>
                                <?php
                                    pg_prepare($db, "get_seasons", "SELECT DISTINCT season FROM match ORDER BY season;");
                                    $seasons_result = pg_execute($db, "get_seasons", array());
                                    while($season = pg_fetch_assoc($seasons_result)):
                                        ?>
                                        <option value="<?php echo $season["season"]; ?>">
                                            <?php echo $season["season"]; ?>
                                        </option>
                                <?php
                                    endwhile;
                                    pg_free_result($seasons_result);
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <input type="submit" class="button is-info" />
            </div>
            <div class="container teams">
                <?php
                    $position = 1;
                    while( $team = pg_fetch_assoc($result) ): ?>
                        <a href="view/team.php?id=<?php echo $team["team"]; ?>" class="list-item team-item">
                            <div class="team-ranking-number"><?php echo $position; ?>°</div>
                            <div class="team-ranking-name">
                                <?php echo $team["longname"]; ?>
                            </div>
                        </a>
                <?php 
                    $position++;
                    endwhile; 
                ?>
            </div>
        </div>
        <style>
            .select-container > .field{
                display: inline-block;
                margin-right: 8px;
            }
            .select-container > input{
                margin-bottom: 0.75rem;
                position: absolute;
                bottom: 0;
            }
            .team-item{
                border: 1px solid black;
                display: flex;
                flex-direction: row;
                font-size: 24px;
            }
            .team-ranking-number{
                width: 64px;
                height: 64px;
                line-height: 64px;
                text-align: center;
                border-right: 1px solid black
            }
            .team-ranking-name{
                padding-left: 24px;
                line-height: 64px;
            }
            #team-first{
                background-color: yellow;
            }
            #team-second{
                background-color: silver;
            }
            #team-third{
                background-color: brown;
            }
        </style>
    </body>
</html>