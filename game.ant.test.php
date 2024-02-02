<?php

include_once 'test.php';
include_once 'game.php';
include_once 'database.php';

describe("Soldier ant implementation", function () {
    describe("A soldier ant moves by sliding an unlimited number of times", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('B', '0,-1');
        $game->playTile('B', '0,2');
        $game->playTile('A', '0,-2');
        $game->pass();
        
        // act
        $game->moveTile('0,-2', '0,3');
        $error1 = $game->getError();
        $game->pass();
        $game->moveTile('0,3', '0,-2');
        $error2 = $game->getError();

        // assert
        assertEqual($error1, null);
        assertEqual($error2, null);
    });
    describe("A soldier ant cannot move to occupied spaces", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('B', '0,-1');
        $game->playTile('B', '0,2');
        $game->playTile('A', '0,-2');
        $game->pass();
        
        // act
        $game->moveTile('0,-2', '0,2');

        // assert
        assertNotEqual($game->getError(), null);
    });
    describe("A soldier ant must move at least one space", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('B', '0,-1');
        $game->playTile('B', '0,2');
        $game->playTile('A', '0,-2');
        $game->pass();
        
        // act
        $game->moveTile('0,-2', '0,-2');

        // assert
        assertNotEqual($game->getError(), null);
    });
    describe("A soldier ant cannot slide to fields that don't have room to slide into", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('B', '-1,0');
        $game->playTile('B', '-1,2');
        $game->playTile('A', '0,-1');
        $game->playTile('B', '-2,2');
        
        // act
        $game->moveTile('0,-1', '-1,1');

        // assert
        assertNotEqual($game->getError(), null);
    });
});