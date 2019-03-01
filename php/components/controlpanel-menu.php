<?php
    $active = "class='is-active'";
    $name = basename($_SERVER['PHP_SELF']);
?>
<aside class="menu controlpanel-menu column">
    <p class="menu-label">General</p>
    <ul class="menu-list">
        <li><a href="<?php echo PAGES; ?>/controlpanel/index.php" <?php echo ($name == 'index.php' || $name == 'view_account.php')? $active: "" ?>>My account</a></li>
        <li><a href="<?php echo PAGES; ?>/controlpanel/view_data.php" <?php echo ($name == 'view_data.php')? $active: "" ?>>View my data</a></li>
    </ul>
    <p class="menu-label">Administrator</p>
    <ul class="menu-list">
        <li><a href="<?php echo PAGES; ?>/controlpanel/add/league.php" <?php echo ($name == 'league.php')? $active: "" ?>>Add leagues</a></li>
        <li><a href="<?php echo PAGES; ?>/controlpanel/add/team.php" <?php echo ($name == 'team.php')? $active: "" ?>>Add teams</a></li>
        <li><a href="<?php echo PAGES; ?>/controlpanel/add/player.php" <?php echo ($name == 'player.php')? $active: "" ?>>Add players</a></li>
        <li><a href="<?php echo PAGES; ?>/controlpanel/add/stats.php" <?php echo ($name == 'stats.php')? $active: "" ?>>Add players' stats</a></li>
        <li><a href="<?php echo PAGES; ?>/controlpanel/import_csv.php" <?php echo ($name == 'import_csv.php')? $active: "" ?>>Import from .csv</a></li>
    </ul>
    <p class="menu-label">Operator</p>
    <ul class="menu-list">
        <li><a href="<?php echo PAGES; ?>/controlpanel/add/match.php" <?php echo ($name == 'match.php')? $active: "" ?>>Add matches</a></li>
        <li><a href="edit_match" <?php echo ($name == 'edit_match.php')? $active: "" ?>>Edit matches</a></li>
    </ul>
    <p class="menu-label">Partner</p>
    <ul class="menu-list">
        <li><a href="<?php echo PAGES; ?>/controlpanel/add/quote.php" <?php echo ($name == 'quote.php')? $active: "" ?>>Add quotes</a></li>
        <li><a href="edit_quote.php" <?php echo ($name == 'edit_quote.php')? $active: "" ?>>Edit quotes</a></li>
    </ul>
</aside>