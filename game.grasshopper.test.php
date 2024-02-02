<?php

include_once 'test.php';
include_once 'game.php';
include_once 'database.php';


describe("Grasshopper implementation", function () {
    describe("A grasshopper moves by making a jump in a straight line to a field immediately behind another stone in the direction of the jump.", function () {
        describe("Vertical jump", function () {
            // arrange
            $db = getDatabase();
            $game = new Game($db);
            $game->restart();
            $game->playTile('Q', '0,0');
            $game->playTile('Q', '0,1');
            $game->playTile('B', '0,-1');
            $game->playTile('B', '0,2');
            $game->playTile('G', '0,-2');
            $game->playTile('B', '0,3');
            assertEqual($game->getError(), null);
            
            // act
            $game->moveTile('0,-2', '0,4');
    
            // assert
            assertEqual($game->getError(), null);
        });
        describe("Horizontal jump", function () {
            // arrange
            $db = getDatabase();
            $game = new Game($db);
            $game->restart();
            $game->playTile('Q', '0,0');
            $game->playTile('Q', '1,0');
            $game->playTile('B', '-1,0');
            $game->playTile('B', '2,0');
            $game->playTile('G', '-2,0');
            $game->playTile('B', '3,0');
            assertEqual($game->getError(), null);

            // act
            $game->moveTile('-2,0', '4,0');

            // assert
            assertEqual($game->getError(), null);
        });

        describe("Diagonal jump", function () {
            // arrange
            $db = getDatabase();
            $game = new Game($db);
            $game->restart();
            $game->playTile('Q', '0,0');
            $game->playTile('Q', '-1,1');
            $game->playTile('G', '1,-1');
            assertEqual($game->getError(), null);
            $game->forcePass();

            // act
            $game->moveTile('1,-1', '-2,2');

            // assert
            assertEqual($game->getError(), null);
        });
    });

    describe("A grasshopper may not move to the field where it is already standing.", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '-1,1');
        $game->playTile('G', '1,-1');
        $game->forcePass();

        // act
        $game->moveTile('1,-1', '1,-1');

        // assert
        assertNotEqual($game->getError(), null);
    });

    describe("A grasshopper must jump over at least one stone.", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '-1,1');
        $game->playTile('G', '1,-1');
        $game->forcePass();
        
        // act
        $game->moveTile('1,-1', '0,-1');

        // assert
        assertNotEqual($game->getError(), null);        
    });

    describe("A grasshopper may not jump to an occupied field.", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('G', '0,-1');
        $game->forcePass();
        
        // act
        $game->moveTile('0,-1', '0,1');

        // assert
        assertNotEqual($game->getError(), null);
    });

    describe("A grasshopper may not jump over empty fields. This means that all fields between the start and end positions must be occupied.", function () {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $game->restart();
        $game->playTile('Q', '0,0');
        $game->playTile('Q', '0,1');
        $game->playTile('G', '0,-1');
        $game->playTile('B', '-1,2');

        // act
        $game->moveTile('0,-1', '-2,3');
        
        // assert
        assertNotEqual($game->getError(), null);
    });
});