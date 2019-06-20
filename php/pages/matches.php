<?php    
    require_once('config.php');
    require_once(LIB . '/database.php');
    require_once(COMPONENTS . '/paginated-select.php');
    require_once(LIB . '/utils.php');
    $db = db_connect();
?>
<!DOCTYPE html>
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
                if( !isset($_GET["q"]) ): ?>
                    <h1 class="title is-1 title-centered">
                        All registered matches
                    </h1>
            <?php
                else: ?>
                <h1 class="title is-1 title-centered">
                    Search results for "<?php echo $_GET["q"]; ?>"
                </h1>
            <?php
                endif; ?>
            <form class="field" method="GET">
                <div class="control">
                    <input 
                        class="input" 
                        type="text" 
                        name="q" 
                        placeholder="Search by team name" />
                </div>
            </form>
            <?php
                $display_match = function($item){
                    return sprintf(
                        "%s - %s - %s in %s",
                        format_date($item["played_on"]), 
                        $item["hometeam_longname"], 
                        $item["awayteam_longname"],
                        $item["league_name"]
                    );
                };
                $link_match = function($item){
                    return "view/match.php?id=" . $item["id"];
                };
                if( !isset($_GET["q"]) ){
                    $total_matches = pg_fetch_assoc(pg_query("SELECT COUNT(*) AS matches FROM match;"))["matches"];
                    create_paginated_select_form(
                        "SELECT 
                            match.*, 
                            home.longname AS hometeam_longname, 
                            away.longname AS awayteam_longname, 
                            league.name AS league_name 
                         FROM match 
                         JOIN team AS home ON home.id = match.hometeam 
                         JOIN team AS away ON away.id = match.awayteam 
                         JOIN league ON league.id = match.league 
                         ORDER BY played_on 
                         DESC LIMIT $1 OFFSET $2", 
                        $total_matches, 
                        $display_match, 
                        $link_match
                    );
                } else {
                    $query = "%" . strtolower(trim($_GET["q"])) . "%";
                    pg_prepare(
                        $db, 
                        "total_matches", 
                        "SELECT 
                            COUNT(*) AS matches 
                         FROM match 
                         JOIN team AS home ON home.id = match.hometeam 
                         JOIN team AS away ON away.id = match.awayteam 
                         WHERE 
                            lower(home.longname) LIKE $1 OR 
                            lower(away.longname) LIKE $1;
                        "
                    );
                    $total_matches = pg_fetch_row(pg_execute($db, "total_matches", array($query)))[0];
                    if( $total_matches == 0 ){
                        echo "Nothing was found with that name...";
                    } else {
                        create_paginated_select_form(
                            "SELECT 
                                 match.*, 
                                home.longname AS hometeam_longname, 
                                away.longname AS awayteam_longname, 
                                league.name AS league_name
                            FROM match 
                            JOIN team AS home ON home.id = match.hometeam 
                            JOIN team AS away ON away.id = match.awayteam 
                            JOIN league ON league.id = match.league
                            WHERE 
                                lower(home.longname) LIKE $1 OR 
                                lower(away.longname) LIKE $1
                            LIMIT $2 OFFSET $3",
                            $total_matches, 
                            $display_match, 
                            $link_match,
                            array($query)
                        );
                    }
                }
            ?>
        </div>
    </body>
</html>