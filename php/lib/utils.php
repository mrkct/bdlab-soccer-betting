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

    /**
     * format_date($date)
     * $date: A string with a date in this format year-month-day
     * Shorthand for calling date_create and then date_format. Returns
     * a string with the date in the european format dd/mm/yyyy
     */
    function format_date($date){
        return date_format(
            date_create("2013-03-15"), 
            "d/m/Y"
        );
    }