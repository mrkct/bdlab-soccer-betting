# BDLab 2019 Project

## What is this
This is a website that allows to see stats about soccer matches and their relative bets. This project was part of the exam for my university databases course. 

**Warning**: In the `extras` folder you can also find things that are not code and should not be included in the server, but are useful. These includes:

- Sample data in CSV to test the website
- A sql dump of the database with the sample data imported and a default user created (username&password: admin)
- (In Italian): a user guide and a technical report on this project
- (In Italian): a PDF with the original assignment

**Last Warning**: There are no XSS protection. I don't care to fix it though, as no one uses this anyway.

## How do I run this
To run this project you first need to have these things installed:

- PHP 7. PHP 5.5 should work, but I can't guarantee it
- PostgreSQL 11 or up.
- A web server, I used Apache but anything should work

When everything is ready, just copy this entire project in your server root and set the following environment variables:

- DB_HOST: host where the database resides
- DB_NAME: name of the database to use
- DB_SCHEMA: schema to use in the database
- DB_USER: user to use to connect to the db
- DB_PASSWORD: password of the user to connect to the db
- DB_PORT: port to use to connect to the db

After having done that you can run your webserver. There are 2 scripts `run.bat`(Windows) and `run.sh`(Linux), they both automatically setup these variables and run Apache. 

At last you can either import the .sql dump in the `/extras` folder and login with the default admin (username: admin, password: admin). Or you can go to `/php/pages/firstsetup.php` where it will automatically run the sql to create all the tables and stuff and also allow you to create the first admin user.