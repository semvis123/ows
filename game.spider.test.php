<?php

include_once 'test.php';
include_once 'game.php';
include_once 'database.php';

describe("Spider implementation", function () {
    describe("Spider should be allowed to make a slide of exactly three tiles", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('B', '-1,0');
        $game->playTile('B', '-1,2');
        $game->playTile('S', '-1,-1');
        $game->playTile('B', '-2,2');
        
        // act
        $game->moveTile('-1,-1', '-1,1');

        // assert
        assertEqual($game->getError(), null);
    });

    describe("Spider should not be able to slide less than three tiles", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('B', '-1,0');
        $game->playTile('B', '-1,2');
        $game->playTile('S', '-1,-1');
        $game->playTile('B', '-2,2');
        
        // act
        $game->moveTile('-1,-1', '-2,0');

        // assert
        assertNotEqual($game->getError(), null);
    });
    describe("Spider should not be able to slide more than three tiles", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('B', '-1,0');
        $game->playTile('B', '-1,2');
        $game->playTile('S', '-1,-1');
        $game->forcePass();
        
        // act
        $game->moveTile('-1,-1', '-3,2');

        // assert
        assertNotEqual($game->getError(), null);        
    });
    describe("Spider should not be able to slide over other tiles", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('B', '-1,0');
        $game->playTile('B', '-1,2');
        $game->playTile('S', '-1,-1');
        $game->playTile('B', '-2,2');
        
        // act
        $game->moveTile('-1,-1', '0,0');

        // assert
        assertNotEqual($game->getError(), null);
    });
});