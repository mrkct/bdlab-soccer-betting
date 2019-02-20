<?php
    /**
     * Creates a nav html element complete with pagination support
     * Parameters:
     * $current_page: The current page, starting from 1
     * $total_pages: The total number of pages
     * $link: A string format representing the link with 1 integer argument for the page
     */
    function create_pagination($current_page, $total_pages, $link){
        ?>
        <nav class="pagination is-centered" role="navigation" aria-label="pagination">
            <?php 
                if( $current_page > 1 ): ?>
                    <a href="<?php echo sprintf($link, $current_page-1); ?>" class="pagination-previous">Previous</a>
            <?php endif; ?>
            <?php
                if( $current_page < $total_pages ): ?>
                    <a href="<?php echo sprintf($link, $current_page+1); ?>"class="pagination-next">Next page</a>
            <?php endif; ?>
            <ul class="pagination-list">
                <?php
                    if( $current_page > 2 ): ?>
                        <li><a href="<?php echo sprintf($link, 1); ?>" class="pagination-link" aria-label="Goto page 1">1</a></li>
                        <li><span class="pagination-ellipsis">&hellip;</span></li>
                <?php endif; ?>
                <?php
                    for($i = $current_page-1; $i < $current_page+2; $i++): 
                        if( $i > 0 && $i <= $total_pages ): ?>
                        <li>
                            <a href="<?php echo sprintf($link, $i); ?>" class="pagination-link <?php echo $i == $current_page? "is-current": ""; ?>" aria-label="Page <?php echo $i; ?>" aria-current="page">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php
                    if( $current_page < $total_pages-1 ): ?>
                        <li>
                            <span class="pagination-ellipsis">&hellip;</span>
                        </li>
                        <li>
                            <a href="<?php echo sprintf($link, $total_pages); ?>" class="pagination-link" aria-label="Goto page <?php echo $total_pages; ?>">
                                <?php echo $total_pages; ?>
                            </a>
                        </li>
                <?php endif; ?>
            </ul>
        </nav>
<?php
    }
