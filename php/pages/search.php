<?php
    require_once('config.php');
    require_once(LIB . '/database.php');
    require_once(LIB . '/utils.php');

    if( !isset($_GET["q"]) ){
        redirect(PAGE_HOME);
        exit(0);
    }

    define('RESULT_ITEMS', 10);

    $db = db_connect();
?>
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
            <h1 class="title is-1 title-centered">
                Search results for "<?php echo $_GET["q"]; ?>" 
            </h1>
            <?php
                $query = "%" . strtolower($_GET["q"]) . "%";
                pg_prepare(
                    $db, 
                    "search_teams", 
                    "SELECT * FROM team WHERE lower(longname) LIKE $1 OR lower(shortname) LIKE $1 ORDER BY longname;"
                );
                pg_prepare(
                    $db,
                    "search_players",
                    "SELECT * FROM player WHERE lower(name) LIKE $1 ORDER BY name;"
                );
                pg_prepare(
                    $db,
                    "search_providers",
                    "SELECT * FROM bet_provider WHERE lower(id) LIKE $1 OR lower(name) LIKE $1 ORDER BY id"
                );

                $teams = pg_execute($db, "search_teams", array($query));
                $players = pg_execute($db, "search_players", array($query));
                $providers = pg_execute($db, "search_providers", array($query));
            ?>

            <section>
                <h3 class="title is-3">
                    Teams
                </h3>
                <div class="list is-hoverable">
                    <?php
                        $total_teams = pg_num_rows($teams);
                        if( $total_teams == 0 ){
                            echo "<div class='list-item'>No teams found with that query</div>";
                        } else {
                            $i = 0;
                            while( $i < RESULT_ITEMS && $row = pg_fetch_assoc($teams) ): ?>
                                <a class="list-item" href="<?php echo PAGES; ?>/view/team.php?id=<?php echo $row["id"]; ?>">
                                    <?php echo $row["longname"]; ?>
                                </a>
                                <?php
                                $i++;
                            endwhile; 
                        }
                        if( $total_teams > RESULT_ITEMS ){
                            echo "<div class='list-item'>and ", ($total_teams - RESULT_ITEMS), " more...</div>";
                        }
                    ?>
                </div>
            </section>
            <section>
                <h3 class="title is-3">
                    Players
                </h3>
                <div class="list is-hoverable">
                    <?php
                        $total_players = pg_num_rows($players);
                        if( $total_players == 0 ){
                            echo "<div class='list-item'>No players found with that query</div>";
                        } else {
                            $i = 0;
                            while( $i < RESULT_ITEMS && $row = pg_fetch_assoc($players) ): ?>
                                <a class="list-item" href="<?php echo PAGES; ?>/view/player.php?id=<?php echo $row["id"]; ?>">
                                    <?php echo $row["name"]; ?>
                                </a>
                                <?php
                                $i++;
                            endwhile; 
                        }
                        if( $total_players > RESULT_ITEMS ){
                            echo "<div class='list-item'>and ", ($total_players - RESULT_ITEMS), " more...</div>";
                        }
                    ?>
                </div>
            </section>
        </div>
    </body>
</html>