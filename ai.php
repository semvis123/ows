<?php

include_once 'game.php';

class AI {
    private $game;
    public function __construct($game) {
        $this->game = $game;
    }

    public function getMove() {
        $url = "http://ai:5123/";
        $data = array(
            "move_number" => $this->game->getMoveNumber(),
            "hand" => $this->game->getHandForAI(),
            "board" => $this->game->getBoard()
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        echo $result;
        return json_decode($result, true);
    }
}