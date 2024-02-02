<?php

function getDatabase() {
    global $database;
    if ($database === null) {
        $database = new mysqli('database', 'root', $_ENV["MYSQL_ROOT_PASSWORD"], 'hive');
    }
    return $database;
}
