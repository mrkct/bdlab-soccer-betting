/**
 *  Data type returned by all functions insert_*
 */
CREATE TYPE QueryResult AS(
    success BOOLEAN,
    error_code INTEGER,
    message VARCHAR(100)
);