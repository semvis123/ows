<?php

include_once 'test.php';
include_once 'game.php';

describe("Tile dropdown should filter out non available tiles", function () {
    // arrange
    $db = include 'database.php';
    $game = new Game($db);
    $game->restart();
    $player1_hand_before = $game->getHand(0);
    assertNotEqual(array_search('Q', array_keys($player1_hand_before)), false);

    // act
    $game->playTile('Q', '0,0');

    // assert
    $player1_hand_after = $game->getHand(0);
    assertEqual(array_search('Q', array_keys($player1_hand_after)), false);
});

describe("New tile position dropdown should filter out non available positions", function () {
    // arrange
    $db = include 'database.php';
    $game = new Game($db);
    $game->restart();
    $game->playTile('Q', '0,0');
    $game->playTile('Q', '0,1');
    
    // act
    $moves_before = $game->getPossiblePlaces();
    $game->playTile('B', '0,-1');
    $moves_after = $game->getPossiblePlaces();
    
    // assert
    assertNotEqual(array_search('0,-1', $moves_before), false);
    assertEqual(array_search('0,-1', $moves_after), false);
});

describe("Move tile dropdown should only show the current players tile", function () {
    // arrange
    $db = include 'database.php';
    $game = new Game($db);
    $game->restart();

    // act
    $game->playTile('Q', '0,0');
    $game->playTile('Q', '0,1');
    $moves = $game->getMovableTiles();

    // assert
    assertEqual(count($moves), 1);
    assertEqual($moves[0], '0,0');
});