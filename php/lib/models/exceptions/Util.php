<?php
require_once(LIB . '/models/exceptions/DBException.php');
require_once(LIB . '/models/exceptions/DuplicateDataException.php');
require_once(LIB . '/models/exceptions/ForeignKeyException.php');
require_once(LIB . '/models/exceptions/PermissionDeniedException.php');

/**
 * result_row_to_exception($resultrow)
 * $resultrow: An associative array returned from one of the defined 
 * plPgSQL function(eg: insert_league)
 * 
 * Takes a result row returned from one of the plPgSQL functions
 * and, if an error occurred, throws the correct exception with the
 * embedded message
 */
function result_row_to_exception($resultrow){
    if( isset($resultrow['success']) && ($resultrow['success'] == 'f' || !$resultrow['success'] )){
        switch( $resultrow['error_code'] ){
        case -1:
            throw new PermissionDeniedException($resultrow['message']);
            break;
        case -2:
            throw new DuplicateDataException($resultrow['message']);
            break;
        case -3:
            throw new ForeignKeyException($resultrow['message']);
            break;
        default:
            throw new DBException($resultrow['message']);
            break;
        }
    }
}

/**
 * Executes a prepared statement and throws an exception on failure.
 * $db: The database connection to execute the query on
 * $queryName: The name of the prepared statement
 * $parameters: An array of parameters to pass to the query
 */
function execute_query($db, $queryName, $parameters){
    $result = @pg_execute($db, $queryName, $parameters);
    if( !$result ){
        throw new DBException(pg_last_error($db));
    }
    $row = pg_fetch_assoc($result);
    result_row_to_exception($row);
    return $row;
}