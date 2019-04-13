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
            date_create($date), 
            "d/m/Y"
        );
    }

    /**
     * Tests 'isset' on all elements in the $keys array
     * on $where. Returns true if all 'isset's were true,
     * false otherwise
     * @param keys: An array of map keys to test on $where
     * @param where: A map to test keys on
     * @return boolean
     */
    function are_set($keys, $where){
        foreach($keys as $key){
            if( !isset($where[$key]) ){
                return false;
            }
        }
        return true;
    }