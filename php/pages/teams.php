<?php require_once('config.php'); ?>
<?php
    require_once(LIB . '/database.php');
    $db = db_connect();
    $pagesize = 10;
    $page = 0;
    $offset = 0;
    if( isset($_GET["pagesize"]) ){
        $pagesize = min(25, intval($_GET["pagesize"]));
    }
    if( isset($_GET["page"]) ){
        $page = max(1, intval($_GET["page"]));
        $offset = ($page-1) * $pagesize;
    }

    $count_result = pg_query($db, 'SELECT COUNT(*) as total_teams FROM team;');
    $total_teams = pg_fetch_row($count_result)[0];
    $total_pages = ceil($total_teams / $pagesize);
    
    pg_prepare(
        $db, 
        'get_teams', 
        'SELECT * FROM team ORDER BY shortname LIMIT $1 OFFSET $2'
    );
    $result = pg_execute($db, 'get_teams', array($pagesize, $offset));
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
            <div class="list is-hoverable">
                <?php
                    while($team = pg_fetch_assoc($result) ):
                        ?>
                        <a href="view/team.php?id=<?php echo $team["id"]; ?>" class="list-item">
                            <?php echo $team["shortname"], ": ", $team["longname"]; ?>
                        </a>
                <?php
                    endwhile;
                ?>
            </div>
            <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                <?php 
                    if( $page > 1 ): ?>
                        <a href="?pagesize=<?php echo $pagesize; ?>&page=<?php echo $page-1; ?>" class="pagination-previous">Previous</a>
                <?php endif; ?>
                <?php
                    if( $page < $total_pages ): ?>
                        <a href="?pagesize=<?php echo $pagesize; ?>&page=<?php echo $page+1; ?>"class="pagination-next">Next page</a>
                <?php endif; ?>

                <ul class="pagination-list">
                    <?php
                        if( $page > 2 ): ?>
                            <li><a href="?pagesize=<?php echo $pagesize; ?>&page=1" class="pagination-link" aria-label="Goto page 1">1</a></li>
                            <li><span class="pagination-ellipsis">&hellip;</span></li>
                    <?php endif; ?>
                    <?php
                        for($i = $page-1; $i < $page+2; $i++): 
                            if( $i > 0 && $i <= $total_pages ): ?>
                            <li>
                                <a href="?pagesize=<?php echo $pagesize; ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $i == $page? "is-current": ""; ?>" aria-label="Page <?php echo $i; ?>" aria-current="page">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php
                        if( $page < $total_pages-1 ): ?>
                            <li>
                                <span class="pagination-ellipsis">&hellip;</span>
                            </li>
                            <li>
                                <a href="?pagesize=<?php echo $pagesize; ?>&page=<?php echo $total_pages; ?>" class="pagination-link" aria-label="Goto page <?php echo $total_pages; ?>">
                                    <?php echo $total_pages; ?>
                                </a>
                            </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </body>
</html>