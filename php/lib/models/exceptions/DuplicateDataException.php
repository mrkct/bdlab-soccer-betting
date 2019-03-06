<?php
require_once('DBException.php');
/**
 * Exception thrown when a duplicate primary key error
 * happens while inserting some data in the db
 */
class DuplicateDataException extends DBException{}