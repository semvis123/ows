<?php

session_start();

include_once 'util.php';
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

$piece = $_POST['piece'];
$to = $_POST['to'];

$hand = $game->getHand($game->getCurrentPlayer());

$game->playTile($piece, $to);
$game->saveStateToSession();

header('Location: index.php');
