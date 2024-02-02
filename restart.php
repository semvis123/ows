<?php

session_start();

include_once 'game.php';
include_once 'database.php';

$db = getDatabase();

$game = new Game($db);
$game->restart();
$game->saveStateToSession();

header('Location: index.php');
