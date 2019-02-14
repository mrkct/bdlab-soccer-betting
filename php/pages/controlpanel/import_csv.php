<?php
    require_once('config.php');
    require_once(COMPONENTS . '/logincheck.php');
    if( !$logged ){
        header('location: /bdlab/php/login.php');
        exit();
    }

    define('TYPE_MATCH', 'match');
    define('TYPE_BET', 'bet');
    define('TYPE_STATS', 'player_attribute');


    $counted_rows = 0;
    $error_rows = 0;
    $error_log = array();
    if( isset($_FILES["file"]) ){
        require_once(LIB . '/database.php');

        $db = db_connect();
        $file = fopen($_FILES["file"]["tmp_name"], "r");

        if( !$file ){
            $error = true;
            $errormsg = "Failed to open file on server";
        } else {
            switch($_POST["type"]){
                case TYPE_MATCH:
                    require_once(LIB . '/csv_import/import_match.php');
                    $result = match_import_csv($file, $db);
                    break;
                case TYPE_STATS:
                    require_once(LIB . '/csv_import/import_stats.php');
                    $result = stats_import_csv($file, $db);
                    break;
                case TYPE_BET:
                    require_once(LIB . '/csv_import/import_bet.php');
                    $result = bet_import($file, $db);
                    break;
                default:
                    $result = array(
                        "total_rows" => 1,
                        "error_rows" => 1,
                        "error_log" => array(
                            0 => array(
                                "line" => 1,
                                "message" => "Unknown import option provided"
                            )
                        )
                    );
            }
            $counted_rows = $result["total_rows"];
            $error_rows = $result["error_rows"];
            $error_log = $result["error_log"];
        }
    }
?>
<!DOCTYPE html>
<html class="has-background-light full-height">
    <head>
        <title>Soccer Bets - Import CSV</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
    </head>
    <body class="has-background-light">
        <?php include_once(COMPONENTS . '/navbar.php'); ?>
        <div class="container">
            <div class="columns">
                <?php require_once(COMPONENTS . '/controlpanel-menu.php'); ?>
                <div class="container column is-three-quarters">
                    <h2 class="title is-2">Import data from CSV</h2>
                    <form class="form controlpanel-form" method="POST" enctype="multipart/form-data">
                        <input type="file" name="file" class="file" />
                        <label class="radio">
                            <input type="radio" name="type" value="<?php echo TYPE_MATCH; ?>" />
                            Match Data (match.csv)
                        </label>
                        <br>
                        <label class="radio">
                            <input type="radio" name="type" value="<?php echo TYPE_BET; ?>" />
                            Bet Data (bet.csv)
                        </label>
                        <br>
                        <label class="radio">
                            <input type="radio" name="type" value="<?php echo TYPE_STATS; ?>" />
                            Player's Stats (player_attribute.csv)
                        </label>
                        <input class="input button is-link" type="submit" value="Import data" />
                    </form>
                    <?php
                        if( $counted_rows > 0 && $error_rows == 0 ):
                            ?>
                        <div class="notification is-success">
                            <?php echo $counted_rows; ?> added successfully.
                        </div>
                        <?php endif; ?>
                    <?php
                        if( $error_rows != 0 ):
                            ?>
                            <div class="notification is-danger">
                                <?php echo $error_rows; ?> out of <?php echo $counted_rows; ?> could not be added because of errors.
                                <button onClick="toggleErrorPanel()" class="button is-link">
                                    <span class="icon is-small">
                                        <i class="fas fa-chevron-circle-down"></i>
                                    </span>
                                    <span>Show more details</span>
                                </button>
                                <pre id="errorPanel" style="display: none"><?php
                                        foreach($error_log as $error){
                                            echo "At line ", $error["line"], ":", $error["message"], "\n";
                                        }
                                    
                              ?></pre>
                            </div>
                        <?php endif; ?>
                </div>
                <script type="text/javascript">
                    function toggleErrorPanel(){
                        var errorPanel = document.getElementById("errorPanel");
                        if( errorPanel.style.display == "block" ){
                            errorPanel.style.display = "none";
                        } else {
                            errorPanel.style.display = "block";
                        }
                    }
                </script>
            </div>
        </div>
    </body>
</html>