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
            <h1 class="title is-1 title-centered">
                All registered players
            </h1>
            <?php
                
                $total_players = pg_fetch_assoc(pg_query("SELECT COUNT(*) AS players FROM player;"))["players"];
                $display_player = function($item){
                    return $item["name"];
                };
                $link_player = function($item){
                    return "view/player.php?id=" . $item["id"];
                };
                create_paginated_select_form(
                    "SELECT * FROM player ORDER BY name LIMIT $1 OFFSET $2", 
                    $total_players, 
                    $display_player, 
                    $link_player
                );
            ?>
        </div>
    </body>
</html>