<?php
    require_once('config.php');
    require_once(COMPONENTS . '/pagination.php');
    
    /**
     * Creates a list of items obtained from executing a query. This is
     * a customizable component used to create a page where a user can
     * choose an item from a paginated list when there are too many items
     * for a simple <select> element. After choosing an item the user is redirected
     * to another page for the real processing.
     * @param $query: A parametrized query with 2 parameters which fetches the items
     * to show. The 2 parameters are the current page and the page size(handled internally)
     * @param $total_items: The total number of items to be shown in all pages. This probably means
     * you will need to make a query with COUNT, something like 'SELECT COUNT(*) AS total FROM table'
     * and pass this number 
     * @param $display_format: A function that takes a result row from a query and returns
     * the string to be shown to the user for each item
     * @param $link_format: A function that takes a result row from a query and returns a string
     * representing the link the user gets redirected to when he chooses an item. This function needs
     * to handle writing the GET parameters(if required) 
     */
    function create_paginated_select_form($query, $total_items, $display_format, $link_format){
        $current_page = isset($_GET['page'])? intval($_GET['page']) : 1;
        $page_size = isset($_GET['page_size'])? intval($_GET['page_size']) : 10;
        $offset = ( $current_page - 1 ) * $page_size;

        $db = db_connect();
        pg_prepare($db, "get_page", $query);
        $result = pg_execute($db, "get_page", array($page_size, $offset));
        $result = pg_fetch_all($result, PGSQL_ASSOC);
?>
    <div class="list is-hoverable paginated-select">
        <?php foreach($result as $item): ?>
            <a class="list-item" href="<?php echo $link_format($item); ?>">
                <?php echo $display_format($item); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php create_pagination($current_page, ceil($total_items / $page_size), "?page=%d&page_size=" . $page_size); ?>
<?php
    }
?>