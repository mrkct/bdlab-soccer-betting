<?php require_once('logincheck.php'); ?>
<?php require_once('config.php'); ?>
<nav class="navbar" role="navigation" aria-label="main navigation">
    <div class="container">
        <div class="navbar-brand">
            <a class="navbar-item" id="navbar-title-container" href="/bdlab/">
                <i class="far fa-futbol"></i>
                <span>Soccer Bets</span>
            </a>
        </div>
        <div class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item" href="/bdlab/">
                    Home
                </a>
                <a class="navbar-item" href="<?php echo PAGES; ?>/rankings.php">
                    Rankings
                </a>
                <a class="navbar-item" href="<?php echo PAGES; ?>/teams.php">
                    Teams
                </a>
                <a class="navbar-item" href="<?php echo PAGES; ?>/players.php">
                    Players
                </a>
            </div>
            <div class="navbar-end">
                <?php
                    if( !$logged ):
                    ?>
                        <div class="navbar-item">
                            <a class="button is-primary" href="<?php echo PAGES; ?>/login.php">
                                Login
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="navbar-item">
                            <a class="button is-info" href="<?php echo PAGES; ?>/controlpanel">
                                Control Panel
                            </a>
                        </div>
                        <div class="navbar-item">
                            <a class="button is-danger" href="<?php echo PAGES; ?>/logout.php">
                                Logout
                            </a>    
                        </div>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</nav>