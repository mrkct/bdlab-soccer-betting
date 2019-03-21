<?php
    /**
     * read_param($param)
     * $param: The parameter to check if it is not null and return
     * Returns the passed argument if it is set and it is not empty.
     * Return NULL otherwise.
     */
    function read_param($param){
        return isset($param) && !empty($param)? $param : NULL;
    }

    /**
     * redirect($page)
     * $page: Where to redirect
     * Shorthand for header('location: ' . $page).
     */
    function redirect($page){
        header('location: ' . $page);
    }