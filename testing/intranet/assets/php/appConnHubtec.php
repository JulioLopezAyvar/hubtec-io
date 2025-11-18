<?php
    $db_host        = 'localhost';
    $db_port        = '3306';
    $db_charset     = 'utf8mb4';
    $db_database    = 'c2880645_hubtec';
    $db_user        = 'c2880645_user';
    $db_password    = '7x7sX4kGkjAiFv2Pp';

    $options = [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $dsn = "mysql:host=$db_host;dbname=$db_database;charset=$db_charset;port=$db_port";

    $conn = new \PDO($dsn, $db_user, $db_password, $options)
?>