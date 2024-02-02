<?php

session_start();

include_once 'util.php';
include_once 'game.php';
include_once 'database.php';

$piece = $_POST['from'];
$to = $_POST['to'];

$db = getDatabase();
$game = new Game($db);
try {
    $game->loadFromSession();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}

$game->moveTile($piece, $to);
$game->saveStateToSession();

header('Location: index.php');
