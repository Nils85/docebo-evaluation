# Docebo evaluation

Challenge for the Backend Developer position at Docebo.

This project target PHP 7 but should also works on PHP 5.6

## Test on PHP built-in web server + SQLite

You only need PHP itself to test on your computer.

- Open a Terminal at the WebRoot folder then type `php -S localhost:8000`  
- Go to the URL http://localhost:8000 with your browser to open index.html (frontend)

## Deploy on Apache web server + MySQL

You need PHP, Apache and MySQL to deploy on a server.

- Create a database on MySQL for the WebApp.  
*If you didn't create tables with `WebRoot/DataAccess/tables.sql`  
and insert data with `WebRoot/DataAccess/data.sql`  
the API should create everything for you at your first request.*

- Edit the config file in `WebRoot/WebApp/Config.php` with your database info
- Configure Apache to serve the WebRoot folder
- Go to the URL http://localhost with your browser to open index.html (frontend)

## Unit testing

Download PHPUnit: https://phar.phpunit.de/phpunit-7.phar  
Move it at the root of the project directory and open a terminal.  
Then type:

`php phpunit-7.5.20.phar tests/DataAccessTest.php` To test a specific class  
or  
`php phpunit-7.5.20.phar tests` To test the whole project