<?php require_once('config.php'); ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Soccer Bets</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
        <link rel="stylesheet" href="<?php echo CSS; ?>/homepage.css">
    </head>
    <body>
        <?php require_once(COMPONENTS . '/logincheck.php'); ?>
        <?php include(COMPONENTS . '/navbar.php'); ?>
        <section class="hero is-primary is-bold is-medium">
            <div class="hero-body">
                <div class="container">
                    <h1 class="title">
                        BDLab Soccer Bets
                    </h1>
                    <h2 class="subtitle">
                        An online interface for a database of soccer matches & betting quotes
                    </h2>
                    <form class="field hp-search" method="GET" action="<?php echo PAGES; ?>/search.php">
                        <div class="control is-large">
                            <input 
                                class="input is-large" 
                                type="text" 
                                placeholder="Search a player or a team"
                                name="q">
                        </div>
                        <button class="button is-link is-large">
                            <span class="icon">
                                <i class="fas fa-search"></i>
                            </span>
                            <span>Search</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </body>
</html>