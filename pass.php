<?php

session_start();

include_once 'game.php';
include_once 'database.php';

$db = getDatabase();

$game = new Game($db);
try {
    $game->loadFromSession();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}
$game->pass();
$game->saveStateToSession();

header('Location: index.php');
