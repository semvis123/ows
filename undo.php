<?php

session_start();

include_once 'game.php';

$db = include 'database.php';

$game = new Game($db);
try {
    $game->loadFromSession();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}

$game->undo();
$game->saveStateToSession();

header('Location: index.php');
