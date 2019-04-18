<?php    
    require_once('config.php');
    require_once(LIB . '/database.php');
    require_once(COMPONENTS . '/paginated-select.php');
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
            <?php
                if( !isset($_GET["q"]) ): ?>
                    <h1 class="title is-1 title-centered">
                        All registered players
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
                        placeholder="Search a player by name" />
                </div>
            </form>
            <?php
                $display_player = function($item){
                    return $item["name"];
                };
                $link_player = function($item){
                    return "view/player.php?id=" . $item["id"];
                };
                if( !isset($_GET["q"]) ){
                    $total_players = pg_fetch_assoc(pg_query("SELECT COUNT(*) AS players FROM player;"))["players"];
                    create_paginated_select_form(
                        "SELECT * FROM player ORDER BY name LIMIT $1 OFFSET $2", 
                        $total_players, 
                        $display_player, 
                        $link_player
                    );
                } else {
                    $query = "%" . strtolower(trim($_GET["q"])) . "%";
                    pg_prepare($db, "total_players", "SELECT COUNT(*) AS players FROM player WHERE lower(name) LIKE $1;");
                    $total_players = pg_fetch_row(pg_execute($db, "total_players", array($query)))[0];
                    if( $total_players == 0 ){
                        echo "Nothing was found with that name...";
                    } else {
                        create_paginated_select_form(
                            "SELECT * FROM player WHERE lower(name) LIKE $1 ORDER BY name LIMIT $2 OFFSET $3", 
                            $total_players, 
                            $display_player, 
                            $link_player,
                            array($query)
                        );
                    }
                }
            ?>
        </div>
    </body>
</html>