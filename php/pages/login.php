<?php
    require_once('config.php');
    require_once(LIB . '/auth.php');

    if( isset($_POST['name']) && isset($_POST['password'])){
        if( $user = verify_login($_POST['name'], $_POST['password']) ){
            session_start();
            $_SESSION['logged'] = true;
            $_SESSION['name'] = $user['name'];
            $_SESSION['id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("location: /bdlab/");
        }
    }
?>
<!DOCTYPE html>
<html class="has-background-light full-height">
    <head>
        <title>Soccer Bets - Collaborator Login</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
    </head>
    <body class="has-background-light">
        <div class="container">
            <?php require_once(COMPONENTS . '/navbar.php'); ?>
            <div class="container login-container">
                <h2 class="title is-2 title-centered">Collaborator Login</h2>
                <form method="POST">
                    <div class="field">
                        <label class="label">Name</label>
                        <div class="control has-icons-left"> 
                            <input class="input is-medium" type="text" name="name" placeholder="Your username here" autofocus/>
                            <span class="icon is-small is-left">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Password</label>
                        <div class="control has-icons-left">
                            <input class="input is-medium" type="password" name="password" placeholder="Your password here" />
                            <span class="icon is-small is-left">
                                <i class="fas fa-key"></i>
                            </span>
                        </div>
                    </div>
                    <div class="field">
                        <input class="input is-medium is-info button" type="submit" value="Login" />
                    </div>
                    <?php
                        if( isset($_POST['name']) || isset($_POST['password'])):
                    ?>
                            <div class="notification is-danger">
                                Wrong username or password provided.
                            </div>
                    <?php 
                        endif; 
                    ?>
                </form>
            </div>
        </div>
    </body>
</html>