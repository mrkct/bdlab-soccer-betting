<?php
require_once('DBException.php');

/**
 * Exception thrown when a foreign key violation
 * happens while inserting data in the db
 */
class ForeignKeyException extends DBException{}