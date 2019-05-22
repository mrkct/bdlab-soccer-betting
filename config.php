<?php
    function try_getenv($name, $default){
        return getenv($name) == NULL ? $default: getenv($name);
    }

    // Edit this if you placed this a subfolder. Don't add trailing spaces
    // example: 'soccer' if you put this in a folder 'soccer'
    define('BASE_PATH', '');
    define('ROOT', __DIR__ );

    // Either define the environment variables or edit the defaults
    // Note that the environment variables take precedence
    // Editing the default values is not suggested
    define('DB_HOST', try_getenv('DB_HOST', 'localhost'));
    define('DB_NAME', try_getenv('DB_NAME', 'soccer'));
    define('DB_SCHEMA', try_getenv('DB_SCHEMA', 'soccer'));
    define('DB_USER', try_getenv('DB_USER', 'postgres'));
    define('DB_PASSWORD', try_getenv('DB_PASSWORD', 'segreto'));
    define('DB_PORT', try_getenv('DB_PORT', 5432) );

    define('COMPONENTS', ROOT . '/php/components');
    define('LIB', ROOT . '/php/lib');

    define('PAGES', BASE_PATH . '/php/pages');
    define('CSS', BASE_PATH . '/css');
    define('JS', BASE_PATH . '/js');
    define('IMAGES', BASE_PATH . '/images');

    // Useful pages for redirects
    define('PAGE_HOME', BASE_PATH . '/');
    define('PAGE_LOGIN', PAGES . '/login.php');
    define('PAGE_FORBIDDEN', PAGES . '/forbidden.php');
?>
