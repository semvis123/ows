<?php

session_start();

include_once 'util.php';

$piece = $_POST['from'];
$to = $_POST['to'];

$db = include 'database.php';
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
