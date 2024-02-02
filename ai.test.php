<?php

include_once 'test.php';
include_once 'game.php';
include_once 'database.php';

class AIMock {
    private $game;
    public function __construct($game) {
        $this->game = $game;
    }

    public function getMove() {
        return ["play", "Q", "0,0"];
    }
}

describe('AI', function() {
    describe('getMove should return a move', function() {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $ai = new AIMock($game);

        // act
        $move = $ai->getMove();

        // assert
        assertNotEqual($move, null);
    });
    describe('getMove should return a possible move', function() {
        // arrange
        $db = getDatabase();
        $game = new Game($db);
        $ai = new AIMock($game);

        // act
        $move = $ai->getMove();

        // assert
        switch ($move[0]) {
            case "play":
                assertEqual(in_array($move[1], ["Q", "S", "B", "A", "G"]), true);
                assertNotEqual(strpos($move[2], ","), false);
                break;
            case "move":
                assertEqual(strpos($move[1], ","), false);
                assertEqual(strpos($move[2], ","), false);
                break;
            case "pass":
                assertEqual($move[1], null);
                assertEqual($move[2], null);
                break;
            default:
                assertEqual(false, true);
        }
    });
});