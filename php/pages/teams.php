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
                All registered teams
            </h1>
            <?php
                $total_teams = pg_fetch_assoc(pg_query("SELECT COUNT(*) AS teams FROM team;"))["teams"];
                $display_team = function($item){
                    return sprintf("%s: %s", $item["shortname"], $item["longname"]);
                };
                $link_team = function($item){
                    return sprintf("view/team.php?id=%d", $item["id"]);
                };
                create_paginated_select_form(
                    "SELECT * FROM team ORDER BY shortname LIMIT $1 OFFSET $2", 
                    $total_teams, 
                    $display_team, 
                    $link_team
                );
            ?>
        </div>
    </body>
</html>