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

describe("Move tile dropdown should only show the current players tiles", function () {
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

describe("Queen move should be considered legal", function () {
    // If white plays a queen bee at (0, 0), and black plays at (1, 0), then it should be a legal move for white to move his queen to (0, 1), but it is not allowed.
    
    // arrange
    $db = include 'database.php';
    $game = new Game($db);
    $game->restart();
    $game->playTile('Q', '0,0');
    $game->playTile('Q', '1,0');
    $moves = $game->getMovableTiles();
    assertEqual(count($moves),1);
    assertEqual($moves[0], '0,0');

    // act
    $game->moveTile('0,0', '0,1');

    // assert
    var_dump($game->getError());
    assertEqual($game->hasError(), false);
});

describe("Queen should be forced to play at move 4 if not played before", function () {
    // arrange
    $db = include 'database.php';
    $game = new Game($db);
    $game->restart();
    $game->playTile('B', '0,0'); // 1
    $game->playTile('Q', '0,1');
    $game->playTile('B', '0,-1'); // 2
    $game->playTile('S', '0,2');
    $game->playTile('S', '0,-2'); // 3
    $game->playTile('B', '0,3');
    assertEqual($game->hasError(), false);
    
    // act
    $game->playTile('B', '0,-3'); // 4
    $error_non_queen = $game->hasError();
    $game->playTile('Q', '0,-3');
    $error_queen = $game->hasError();

    // assert
    assertEqual($error_non_queen, true);
    assertEqual($error_queen, false);
});

describe("Tile should be allowed to move on top of other tiles", function () {
    // arrange
    $db = include 'database.php';
    $game = new Game($db);
    $game->restart();
    $game->playTile('Q', '0,0');
    $game->playTile('Q', '0,1');
    $game->playTile('B', '0,-1');
    $game->playTile('B', '0,2');
    
    // act
    $game->moveTile('0,-1', '0,0');
    $movementError1 = $game->hasError();
    $game->moveTile('0,2', '0,1');
    $movementError2 = $game->hasError();

    // assert
    assertEqual($movementError1, false);
    assertEqual($movementError2, false);
});

describe("Tile should be allowed to be placed in spots that were previously occupied", function () {
    // arrange
    $db = include 'database.php';
    $game = new Game($db);
    $game->restart();
    $game->playTile('Q', '0,0');
    $game->playTile('Q', '0,1');
    $game->playTile('B', '0,-1');
    $game->playTile('B', '0,2');
    $game->moveTile('0,-1', '0,0');
    $game->moveTile('0,2', '0,1');
    
    // act
    $game->playTile('B', '0,-1');
    $placementError1 = $game->hasError();
    $game->playTile('B', '0,2');
    $placementError2 = $game->hasError();

    // assert
    assertEqual($placementError1, false);
    assertEqual($placementError2, false);
});


describe("Grasshopper implementation", function () {
    describe("A grasshopper moves by making a jump in a straight line to a field immediately behind another stone in the direction of the jump.", function () {
        // TODO: add test
    });

    describe("A grasshopper may not move to the field where it is already standing.", function () {
        // TODO: add test
    });

    describe("A grasshopper must jump over at least one stone.", function () {
        // TODO: add test
    });

    describe("A grasshopper may not jump to an occupied field.", function () {
        // TODO: add test
    });

    describe("A grasshopper may not jump over empty fields. This means that all fields between the start and end positions must be occupied.", function () {
        // TODO: add test
    });
});