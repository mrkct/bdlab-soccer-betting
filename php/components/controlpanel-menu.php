<?php
    $active = "class='is-active'";
    $name = basename($_SERVER['PHP_SELF']);
?>
<aside class="menu controlpanel-menu column">
    <p class="menu-label">General</p>
    <ul class="menu-list">
        <li><a href="index.php" <?php echo ($name == 'index.php' || $name == 'view_account.php')? $active: "" ?>>My account</a></li>
        <li><a href="view_data.php" <?php echo ($name == 'view_data.php')? $active: "" ?>>View my data</a></li>
    </ul>
    <p class="menu-label">Administrator</p>
    <ul class="menu-list">
        <li><a href="add_league.php" <?php echo ($name == 'add_league.php')? $active: "" ?>>Add leagues</a></li>
        <li><a href="add_team.php" <?php echo ($name == 'add_team.php')? $active: "" ?>>Add teams</a></li>
        <li><a href="add_player.php" <?php echo ($name == 'add_player.php')? $active: "" ?>>Add players</a></li>
        <li><a href="add_stats.php" <?php echo ($name == 'add_stats.php')? $active: "" ?>>Add players' stats</a></li>
        <li><a href="import_csv.php" <?php echo ($name == 'import_csv.php')? $active: "" ?>>Import from .csv</a></li>
    </ul>
    <p class="menu-label">Operator</p>
    <ul class="menu-list">
        <li><a href="add_match.php" <?php echo ($name == 'add_match.php')? $active: "" ?>>Add matches</a></li>
        <li><a href="edit_match" <?php echo ($name == 'edit_match.php')? $active: "" ?>>Edit matches</a></li>
    </ul>
    <p class="menu-label">Partner</p>
    <ul class="menu-list">
        <li><a href="add_quote.php" <?php echo ($name == 'add_quote.php')? $active: "" ?>>Add quotes</a></li>
        <li><a href="edit_quote.php" <?php echo ($name == 'edit_quote.php')? $active: "" ?>>Edit quotes</a></li>
    </ul>
</aside>