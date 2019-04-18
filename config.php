<?php

    function try_getenv($name, $default){
        return getenv($name) == NULL ? $default: getenv($name);
    }

    define('DB_HOST', try_getenv('DB_HOST', 'localhost'));
    define('DB_NAME', try_getenv('DB_NAME', 'soccer'));
    define('DB_SCHEMA', try_getenv('DB_SCHEMA', 'soccer'));
    define('DB_USER', try_getenv('DB_USER', 'postgres'));
    define('DB_PASSWORD', try_getenv('DB_PASSWORD', 'segreto'));
    define('DB_PORT', try_getenv('DB_PORT', 5432) );

    define('ROOT', __DIR__ );

    define('PREFIX', '/bdlab/php');

    define('COMPONENTS', ROOT . '/php/components');
    define('LIB', ROOT . '/php/lib');

    define('PAGES', '/bdlab/php/pages');
    define('CSS', '/bdlab/css');
    define('JS', '/bdlab/js');
    define('IMAGES', '/bdlab/images');

    // Useful pages for redirects
    define('PAGE_HOME', '/bdlab');
    define('PAGE_LOGIN', PAGES . '/login.php');
    define('PAGE_FORBIDDEN', PAGES . '/forbidden.php');
?>
