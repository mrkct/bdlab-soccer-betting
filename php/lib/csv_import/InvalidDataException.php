<?php
/**
 * An exception to be thrown when, while inserting a
 * csv row in the db, the row cannot be inserted
 * because of errors impossible to solve. 
 */
class InvalidDataException extends Exception {}