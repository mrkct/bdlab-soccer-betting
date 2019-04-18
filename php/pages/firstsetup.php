<?php require_once('config.php'); ?>
<!DOCTYPE html5>
<html>
    <head>
        <title>Soccer Bets - First Setup</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
    </head>
    <body>
        <div class="container">
            <h1 class="title is-1">First Setup</h1>
            <p class="paragraph">
                This page is setting up all the stuff to use this application.
                Don't forget to limit access to this page(or delete it) when you're done.
            </p>
            <pre>
                <?php
                    require_once(LIB . '/database.php');

                    $db = db_connect();

                    $sqlpath = ROOT . '/sql';
                    $sqlfiles = array(
                        '/migrate.sql',
                        '/most_recent_stats.sql',
                        '/get_match_mvp.sql',
                        '/rankings.sql',
                        '/queryresults.sql',
                        '/insert_operations/insert_betprovider.sql',
                        '/insert_operations/insert_league.sql',
                        '/insert_operations/insert_match.sql',
                        '/insert_operations/insert_played.sql',
                        '/insert_operations/insert_player.sql',
                        '/insert_operations/insert_quote.sql',
                        '/insert_operations/insert_stats.sql',
                        '/insert_operations/insert_team.sql',
                        '/delete_operations/delete_betprovider.sql',
                        '/delete_operations/delete_league.sql',
                        '/delete_operations/delete_match.sql',
                        '/delete_operations/delete_played.sql',
                        '/delete_operations/delete_player.sql',
                        '/delete_operations/delete_quote.sql',
                        '/delete_operations/delete_stats.sql',
                        '/delete_operations/delete_team.sql',
                        '/edit_operations/edit_betprovider.sql',
                        '/edit_operations/edit_league.sql',
                        '/edit_operations/edit_match.sql',
                        '/edit_operations/edit_player.sql',
                        '/edit_operations/edit_quote.sql',
                        '/edit_operations/edit_stats.sql',
                        '/edit_operations/edit_team.sql'
                    );

                    foreach($sqlfiles as $filepath){
                        echo "Running ", $filepath, "...";
                        $file = fopen($sqlpath . $filepath, "r");
                        $query = "";
                        while( $buffer = fread($file, 1024) ){
                            $query .= $buffer;
                        }
                        pg_query($db, $query);
                        fclose($file);
                        echo "done!<br>";
                    }

                    $result = pg_query($db, "SELECT COUNT(*) AS users FROM collaborator;");
                    $total_users = pg_fetch_assoc($result)["users"];

                    if( $total_users == 0 && isset($_POST["name"]) && isset($_POST["password"]) ){
                        pg_prepare($db, "create-admin", "INSERT INTO collaborator(name, password, role) VALUES ($1, $2, $3);");
                        pg_execute(
                            $db, 
                            "create-admin", 
                            array(
                                $_POST["name"], 
                                password_hash($_POST['password'], PASSWORD_DEFAULT),
                                "administrator"
                            )
                        );
                        $success = true;
                    }
                ?>
            </pre>
            <?php
                if( $total_users == 0 && !isset($success) ): ?>
                    <form class="form-container" method="POST">
                        <h2 class="title is-2">
                            Create the first administrator account
                        </h2>

                        <div class="field">
                            <label class="label">Login Name</label>
                            <div class="control">
                                <input class="input" name="name" required />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Password</label>
                            <div class="control">
                                <input class="input" name="password" required />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Confirm Password</label>
                            <div class="control">
                                <input class="input" name="confirm-password" required />
                            </div>
                        </div>
                        <div class="field">
                            <div class="control">
                                <input class="input button is-link" type="submit" value="Create account" />
                            </div>
                        </div>
                    </form>
            <?php
                else: ?>
                <?php
                    if( !isset($success) ): ?>
                        <h2 class="title is-2">
                            You have already created an administrator account.
                        </h2>
                        <p class="paragraph">
                            If you need to create other accounts login in your control panel and do it there.
                            This page won't work anymore.
                        </p>
                <?php
                    else: ?>
                    <div class="notification is-success">
                        <h2 class="title is-2">
                            You're all done!
                        </h2>
                        <p class="paragraph">
                            You can go back to the homepage and login with your new account. 
                            Don't forget to delete or disable access to this page when you're done.
                        </p>
                    </div>
                <?php 
                    endif; ?>
            <?php
                endif; ?>
        </div>
        <?php
            
        ?>
    </body>
</html>