<?php
    require_once('config.php');
    require_once(LIB . '/models/loggeduser.php');

    
    $active = "class='is-active'";
    $name = basename($_SERVER['PHP_SELF']);
    // This gets the first folder PHP_SELF is in
    // eg: /usr/bin/php/hello.php ==> /php
    $folder_path = dirname($_SERVER['PHP_SELF']);
    $folder = substr($folder_path, strrpos($folder_path, "/"));
?>
<aside class="menu controlpanel-menu column">
    <p class="menu-label">General</p>
    <ul class="menu-list">
        <li><a href="<?php echo PAGES; ?>/controlpanel/index.php" <?php echo ($name == 'index.php' || $name == 'view_account.php')? $active: "" ?>>My account</a></li>
    </ul>
    <?php
        if( LoggedUser::getRole() == ROLE_ADMIN ): ?>
            <p class="menu-label">Administrator</p>
            <ul class="menu-list">
                <li><a href="<?php echo PAGES; ?>/controlpanel/add/league.php" <?php echo ($name == 'league.php' && $folder == '/add')? $active: "" ?>>Add leagues</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/edit/league.php" <?php echo ($name == 'league.php' && $folder == '/edit')? $active: "" ?>>Edit leagues</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/add/team.php" <?php echo ($name == 'team.php' && $folder == '/add')? $active: "" ?>>Add teams</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/edit/team.php" <?php echo ($name == 'team.php' && $folder == '/edit')? $active: "" ?>>Edit teams</a></li>                
                <li><a href="<?php echo PAGES; ?>/controlpanel/add/player.php" <?php echo ($name == 'player.php' && $folder == '/add')? $active: "" ?>>Add players</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/edit/player.php" <?php echo ($name == 'player.php' && $folder == '/edit')? $active: "" ?>>Edit players</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/add/stats.php" <?php echo ($name == 'stats.php' && $folder == '/add')? $active: "" ?>>Add players' stats</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/edit/stats.php" <?php echo ($name == 'stats.php' && $folder == '/edit')? $active: "" ?>>Edit players' stats</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/import_csv.php" <?php echo ($name == 'import_csv.php')? $active: "" ?>>Import from .csv</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/add/collaborator.php" <?php echo ($name == 'collaborator.php')? $active: "" ?>>Create a new account</a></li>
            </ul>
    <?php
        endif;
        if( LoggedUser::getRole() == ROLE_ADMIN || LoggedUser::getRole() == ROLE_OPERATOR ): ?>
            <p class="menu-label">Operator</p>
            <ul class="menu-list">
                <li><a href="<?php echo PAGES; ?>/controlpanel/add/match.php" <?php echo ($name == 'match.php' && $folder == '/add')? $active: "" ?>>Add matches</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/edit/match.php" <?php echo ($name == 'match.php' && $folder == '/edit')? $active: "" ?>>Edit matches</a></li>
            </ul>
    <?php
        endif;
        if( LoggedUser::getRole() == ROLE_ADMIN || LoggedUser::getRole() == ROLE_PARTNER ): ?>
            <p class="menu-label">Partner</p>
            <ul class="menu-list">
                <li><a href="<?php echo PAGES; ?>/controlpanel/add/quote.php" <?php echo ($name == 'quote.php' && $folder == '/add')? $active: "" ?>>Add quotes</a></li>
                <li><a href="<?php echo PAGES; ?>/controlpanel/edit/quote.php" <?php echo ($name == 'quote.php' && $folder == '/edit')? $active: "" ?>>Edit quotes</a></li>
            </ul>
    <?php
        endif; ?>
</aside>