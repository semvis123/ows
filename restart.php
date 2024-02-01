<?php

session_start();

include_once 'game.php';

$db = include 'database.php';

$game = new Game($db);
$game->restart();
$game->saveStateToSession();

header('Location: index.php');
