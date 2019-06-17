<?php
    require_once('config.php');
    require_once(LIB . '/utils.php');
    require_once(LIB . '/models/loggeduser.php');
    require_once(COMPONENTS . '/messages.php');
    require_once(COMPONENTS . '/logincheck.php');
    if( !$logged ){
        redirect(PAGE_LOGIN);
        exit();
    }

    require_once(LIB . '/database.php');
    $db = db_connect();
    $success = false;
    if( are_set(['name', 'password', 'role', 'affiliation'], $_POST) ) {
        if( LoggedUser::getRole() == ROLE_ADMIN ){
            pg_prepare(
                $db,
                'find_collaborator',
                'SELECT * FROM collaborator WHERE name = $1'
            );
            pg_prepare(
                $db,
                'insert_collaborator',
                'INSERT INTO collaborator(name, password, role, affiliation) VALUES ($1, $2, $3, $4);'
            );
            $result = pg_execute($db, 'find_collaborator', array($_POST['name']));
            if( $result && pg_num_rows($result) == 0 ){
                $result = pg_execute(
                    $db, 
                    'insert_collaborator', 
                    array(
                        $_POST['name'], 
                        password_hash($_POST['password'], PASSWORD_DEFAULT),
                        $_POST['role'],
                        $_POST['role'] == 'partner'? $_POST['affiliation']: NULL
                    )
                );
                $success = true;
            } else {
                $success = false;
                $message = "There is already a user with that name";
            }
            if ( !$result ) {
                $success = false;
                $message = "There was an error with the database. Try later";
            }
        }
    }
?>
<!DOCTYPE html>
<html class="has-background-light full-height">
    <head>
        <title>Soccer Bets - Control Panel</title>
        <?php require_once(COMPONENTS . '/head-imports.php'); ?>
    </head>
    <body class="has-background-light">
        <?php include_once(COMPONENTS . '/navbar.php'); ?>
        <div class="container">
            <div class="columns">
                <?php require_once(COMPONENTS . '/controlpanel-menu.php'); ?>
                <div class="container column is-three-quarters form-container">
                    <h2 class="title is-2 title-centered">Add Collaborator</h2>
                    <form method="POST" class="controlpanel-form">
                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control">
                                <input class="input" name="name" required />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Password</label>
                            <div class="control">
                                <input class="input" type="password" name="password" required />
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Role</label>
                            <div class="select max-width">
                                <select class="max-width" name="role" onChange="roleUpdate()">
                                    <option value="administrator">Administrator</option>
                                    <option value="operator">Operator</option>
                                    <option value="partner">Partner</option>
                                </select>
                            </div>
                        </div>
                        <div class="field" id="affiliation-field" style="display: none">
                            <label class="label">Affiliation</label>
                            <div class="select max-width">
                                <select class="max-width" name="affiliation">
                                    <option value="">None</option>
                                    <?php
                                        $betproviders = pg_query("SELECT * FROM bet_provider ORDER BY name, id;");
                                        while($row = pg_fetch_assoc($betproviders)): ?>
                                            <option value="<?php echo $row["id"]; ?>">
                                                <?php
                                                    echo ($row["name"] == NULL) ? $row["id"] : $row["name"]; ?>
                                            </option>
                                    <?php
                                        endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <?php
                            if( LoggedUser::getRole() == ROLE_ADMIN ): ?>
                                <div class="field">
                                    <div class="control">
                                        <input class="input button is-link" type="submit" value="Insert data" />
                                    </div>
                                </div>
                        <?php
                            else: ?>
                            <?php
                                create_message("<strong>Warning: </strong> You are not allowed to create other users.", MSG_WARNING);
                            ?>
                        <?php
                            endif; ?>
                        <?php 
                            if ( $success ): ?>
                                <div class="notification is-success">
                                    New collaborator successfully added
                                </div>
                            <?php else: ?>
                                <div class="notification is-danger">
                                    <?php echo $message; ?>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <script type="text/javascript">
        function roleUpdate() {
            var role = document.getElementsByName("role")[0].value;
            if (role === "administrator" || role === "operator" ) {
                document.getElementById("affiliation-field").style = "display: none;";
            } else {
                document.getElementById("affiliation-field").style = "display: block;"
            }
        }
        </script>
    </body>
</html>