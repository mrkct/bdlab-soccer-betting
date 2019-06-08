<?php    
    require_once('config.php');
    require_once(LIB . '/database.php');
    require_once(COMPONENTS . '/paginated-select.php');
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
                        All registered teams
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
                        placeholder="Search a team by name" />
                </div>
            </form>
            <?php
                
                $display_team = function($item){
                    return sprintf("%s: %s", $item["shortname"], $item["longname"]);
                };
                $link_team = function($item){
                    return sprintf("view/team.php?id=%d", $item["id"]);
                };
                if( !isset($_GET["q"]) ){
                    $total_teams = pg_fetch_assoc(pg_query("SELECT COUNT(*) AS teams FROM team;"))["teams"];
                    create_paginated_select_form(
                        "SELECT * FROM team ORDER BY shortname LIMIT $1 OFFSET $2", 
                        $total_teams, 
                        $display_team, 
                        $link_team
                    );
                } else {
                    $query = "%" . strtolower(trim($_GET["q"])) . "%";
                    pg_prepare($db, "total_teams", "SELECT COUNT(*) AS teams FROM team WHERE lower(shortname) LIKE $1 OR lower(longname) LIKE $1;");
                    $total_teams= pg_fetch_row(pg_execute($db, "total_teams", array($query)))[0];
                    if( $total_teams == 0 ){
                        echo "Nothing was found with that name...";
                    } else {
                        create_paginated_select_form(
                            "SELECT * FROM team WHERE lower(shortname) LIKE $1 OR lower(longname) LIKE $1 ORDER BY longname LIMIT $2 OFFSET $3", 
                            $total_teams, 
                            $display_team, 
                            $link_team,
                            array($query)
                        );
                    }
                }
                
            ?>
        </div>
    </body>
</html>