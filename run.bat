@echo off
REM Sets environment variables and runs apache
REM Insert your data here

SET DB_HOST=localhost
SET DB_NAME=soccer
SET DB_SCHEMA=soccer
SET DB_USER=postgres
SET DB_PASSWORD=segreto
SET DB_PORT=5432

@echo on

echo Running with db %DB_HOST%:%DB_PORT% on %DB_NAME%.%DB_SCHEMA% with user %DB_USER%
httpd