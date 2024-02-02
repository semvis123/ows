<?php

session_start();

include_once 'util.php';
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

$piece = $_POST['piece'];
$to = $_POST['to'];

$hand = $game->getHand($game->getCurrentPlayer());

$game->playTile($piece, $to);
$game->saveStateToSession();

header('Location: index.php');
