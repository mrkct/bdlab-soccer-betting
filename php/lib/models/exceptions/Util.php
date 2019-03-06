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
    if( $resultrow['success'] == 'f' || !$resultrow['success'] ){
        switch( $resultrow['error_code'] ){
        case -1:
            throw new PermissionDeniedException($resultrow['message']);
            break;
        case -2:
            throw new DuplicateDataExcetion($resultrow['message']);
            break;
        default:
            throw new DBException($resultrow['message']);
            break;
        }
    }
}