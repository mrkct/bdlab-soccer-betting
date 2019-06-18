#!/bin/sh

export DB_HOST=localhost;
export DB_NAME=bdlab
export DB_SCHEMA=soccer
export DB_USER=postgres
export DB_PASSWORD=segreto
export DB_PORT=5432

echo "Running with db $DB_HOST:$DB_PORT on $DB_NAME.$DB_SCHEMA with user $DB_USER";
httpd