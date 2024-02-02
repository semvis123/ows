<?php

session_start();

include_once 'util.php';
include_once 'game.php';
include_once 'database.php';
include_once 'ai.php';

$db = getDatabase();

$game = new Game($db);
try {
    $game->loadFromSession();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}

$ai = new AI($game);

$move = $ai->getMove();

switch ($move[0]) {
    case "play":
        $piece = $move[1];
        $to = $move[2];
        $game->playTile($piece, $to);
        break;
    case "move":
        $piece = $move[1];
        $to = $move[2];
        $game->moveTile($piece, $to);
        break;
    case "pass":
        $piece = null;
        $to = null;
        $game->pass();
        break;
    default:
        $game->setError("Invalid move from AI");
}

$game->saveStateToSession();

header('Location: index.php');
