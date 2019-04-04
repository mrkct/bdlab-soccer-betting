<?php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'soccer');
    define('DB_SCHEMA', 'soccer');
    define('DB_USER', 'postgres');
    define('DB_PASSWORD', 'segreto');
    define('DB_PORT', 5432);

    // Non è una buona idea questo probabilmente
    // set_include_path(__DIR__);
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
