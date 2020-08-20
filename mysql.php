<?php

require_once __DIR__ . '/config.php';

$dsn = 'mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DATABASE.';port='.MYSQL_PORT;
try {
    $pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASSWORD);
} catch (Exception $e) {
    die("Unable to connect to database.".PHP_EOL);
}
