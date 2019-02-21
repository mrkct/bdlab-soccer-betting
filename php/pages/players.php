<?php require_once('config.php'); ?>
<?php
    require_once(LIB . '/database.php');
    $db = db_connect();
    $pagesize = 10;
    $page = 1;
    $offset = 0;
    if( isset($_GET["pagesize"]) ){
        $pagesize = min(25, intval($_GET["pagesize"]));
    }
    if( isset($_GET["page"]) ){
        $page = max(1, intval($_GET["page"]));
    }

    $count_result = pg_query($db, 'SELECT COUNT(*) as total_players FROM player;');
    $total_teams = pg_fetch_row($count_result)[0];
    $total_pages = ceil($total_teams / $pagesize);
    
    $page = min($page, $total_pages);
    $offset = ($page-1) * $pagesize;
    pg_prepare(
        $db, 
        'get_players', 
        'SELECT * FROM player ORDER BY name LIMIT $1 OFFSET $2'
    );
    $result = pg_execute($db, 'get_players', array($pagesize, $offset));
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
            <div class="list is-hoverable">
                <?php
                    while($player = pg_fetch_assoc($result) ):
                        ?>
                        <a href="view/player.php?id=<?php echo $player["id"]; ?>" class="list-item">
                            <?php echo $player["name"]; ?>
                        </a>
                <?php
                    endwhile;
                ?>
            </div>
            <?php
                require_once(COMPONENTS . '/pagination.php');
                create_pagination($page, $total_pages, "?page=%d&pagesize=" . $pagesize);
            ?>
        </div>
    </body>
</html>