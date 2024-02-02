<?php

include_once 'util.php';

class Game {
    private $game_id = 0;
    public $board = array();
    private $hand = array();
    private $error = null;
    public $current_player = 0;
    private $database;
    private $last_move = 0;

    public function __construct($database) {
        $this->database = $database;
    }

    public function loadFromSession() {
        if (!isset($_SESSION['board'])) {
            throw new Exception('no state available');
        }
        $this->board = $_SESSION['board'];
        $this->current_player = $_SESSION['player'];
        $this->hand = $_SESSION['hand'];
        $this->error = $_SESSION['error'];
        $this->game_id = $_SESSION['game_id'];
        $this->last_move = $_SESSION['last_move'];
    }

    public function serializeState() {
        return serialize([$this->hand, $this->board, $this->current_player]);
    }

    public function loadState($state) {
        list($this->hand, $this->board, $this->current_player) = unserialize($state);
    }

    public function saveStateToSession() {
        $_SESSION['board'] = $this->board;
        $_SESSION['player'] = $this->current_player;
        $_SESSION['hand'] = $this->hand;
        $_SESSION['error'] = $this->error;
        $_SESSION['game_id'] = $this->game_id;
        $_SESSION['last_move'] = $this->last_move;
    }

    public function getBoardHtml() {
        $min_x = 1000;
        $min_y = 1000;
        foreach ($this->board as $pos => $tile) {
            list($x, $y) = $this->parsePos($pos);
            if ($x < $min_x) $min_x = $x;
            if ($y < $min_y) $min_y = $y;
        }
        $boardStr = "";
        foreach (array_filter($this->board) as $pos => $tile) {
            list($x, $y) = $this->parsePos($pos);
            $h = count($tile);
            $player = $tile[$h-1][0];
            $stacked = $h > 1 ? ' stacked' : '';
            $left = ($x - $min_x) * 4 + ($y - $min_y) * 2;
            $top = ($y - $min_y) * 4;
            $value = $tile[$h-1][1];
    
            $boardStr .= "<div class=\"tile player$player$stacked\" style=\"left: {$left}em; top: {$top}em;\">($x,$y)<span>$value</span></div>";
        }

        return $boardStr;
    }

    public function getPossibleMoves() {
        $to = [];
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            foreach (array_keys($this->board) as $pos) {
                $pq2 = explode(',', $pos);
                $to[] = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);
            }
        }
        $to = array_unique($to);
        if (!count($to)) $to[] = '0,0';
        return $to;
    }

    public function getMovableTiles() {
        if ($this->hand[$this->getCurrentPlayer()]['Q']) {
            return [];
        }

        $to = [];
        foreach (array_keys($this->board) as $pos) {
            // continue if the tile is on top is not from the current player
            $tile = $this->board[$pos][count($this->board[$pos]) - 1];
            if ($tile[0] != $this->getCurrentPlayer()) {
                continue;
            }
            $to[] = $pos;
        }
        return $to;
    }

    public function getPossiblePlaces() {
        $to = [];
        $hand = $this->getHand($this->getCurrentPlayer());
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            foreach (array_keys($this->board) as $pos) {
                list($x, $y) = explode(',', $pos);
                $new = ($pq[0] + $x).','.($pq[1] + $y);

                if (isset($this->board[$new])) {
                    continue;
                }
                if (count($this->board) && !hasNeighBour($new, $this->board)) {
                    continue;
                }
                if (array_sum($hand) < 11 && !neighboursAreSameColor($this->getCurrentPlayer(), $new, $this->board)) {
                    continue;
                }
                $to[] = $new;
            }
        }
        $to = array_unique($to);
        if (!count($to)) $to[] = '0,0';
        return $to;
    }

    private function parsePos($pos) {
        return explode(',', $pos);
    }

    public function getPlayerName($player) {
        return $player == 0 ? 'White' : 'Black';
    }

    public function getCurrentPlayer() {
        return $this->current_player;
    }

    public function getMoveHistoryhtml() {
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE game_id = ?');
        $stmt->bind_param('i', $this->game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $html = "";
        while ($row = $result->fetch_array()) {
            $html .= '<li>'.$row[2].' '.$row[3].' '.$row[4].'</li>';
        }
        return $html;
    }

    public function getHand($player) {
        return array_filter($this->hand[$player], function ($ct) { return $ct > 0; });
    }

    public function getHandHtml($player) {
        $html = "";
        foreach ($this->hand[$player] as $tile => $ct) {
            for ($i = 0; $i < $ct; $i++) {
                $html .= '<div class="tile player'.$player.'"><span>'.$tile."</span></div> ";
            }
        }
        return $html;    
    }

    public function hasError() {
        return $this->error !== null;
    }

    public function getError() {
        return $this->error;
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function clearError() {
        $this->error = null;
    }

    public function playTile($piece, $to) {
        $this->clearError();
        
        $hand = $this->getHand($this->getCurrentPlayer());
        
        if (!$hand[$piece]) {
            $this->setError("Player does not have tile");
        } elseif (isset($this->board[$to])) {
            $this->setError('Board position is not empty');
        } elseif (count($this->board) && !hasNeighBour($to, $this->board)) {
            $this->setError("board position has no neighbour");
        } elseif (array_sum($hand) < 11 && !neighboursAreSameColor($this->getCurrentPlayer(), $to, $this->board)) {
            $this->setError("Board position has opposing neighbour");
        } elseif (array_sum($hand) <= 8 && $hand['Q'] && $piece != 'Q') {
            $this->setError("Must play queen bee");
        }
        if ($this->hasError()) return;

        $this->board[$to] = [[$this->getCurrentPlayer(), $piece]];
        $this->hand[$this->getCurrentPlayer()][$piece]--;
        $this->current_player = $this->getCurrentPlayer() == 0 ? 1 : 0;
        $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, ?, "play", ?, ?, ?)');
        $stmt->bind_param('issis', $this->game_id, $piece, $to, $this->last_move, $this->serializeState());
        $stmt->execute();
        $this->last_move = $this->database->insert_id;
    }

    public function setOtherPlayer() {
        $this->current_player = 1 - $this->current_player; // 0 -> 1, 1 -> 0
    }

    public function moveTile($piece, $to) {
        $this->clearError();
        $hand = $this->getHand($this->getCurrentPlayer());

        if (!isset($this->board[$piece])) {
            $this->setError('Board position is empty');
        } elseif ($this->board[$piece][count($this->board[$piece]) - 1][0] != $this->getCurrentPlayer()) {
            $this->setError("Tile is not owned by player");
        } elseif ($hand['Q']) {
            $this->setError("Queen bee is not played");
        }
        if ($this->hasError()) return;

        $tile = array_pop($this->board[$piece]);
        if (!hasNeighBour($to, $this->board)) {
            $this->setError("Move would split hive");
        } else {
            $all = array_keys($this->board);
            $queue = [array_shift($all)];
            while ($queue) {
                $next = explode(',', array_shift($queue));
                foreach ($GLOBALS['OFFSETS'] as $pq) {
                    list($p, $q) = $pq;
                    $p += $next[0];
                    $q += $next[1];
                    if (in_array("$p,$q", $all)) {
                        $queue[] = "$p,$q";
                        $all = array_diff($all, ["$p,$q"]);
                    }
                }
            }
            if ($all) {
                $this->setError("Move would split hive");
            } else {
                if ($piece == $to) {
                    $this->setError('Tile must move');
                } elseif (isset($this->board[$to]) && $tile[1] != "B") {
                    $this->setError('Tile not empty');
                } elseif ($tile[1] == "Q" || $tile[1] == "B") {
                    if (!slide($this->board, $piece, $to)) {
                        $this->setError('Tile must slide');
                    }
                } elseif ($tile[1] == "G") {
                    if (isNeighbour($piece, $to)) {
                        $this->setError('Jump must greater than 1');
                    } elseif (pathContainsEmptyTiles($piece, $to, $this->board)) {
                        $this->setError('Path contains empty tiles');
                    }
                }
            }
        }
        if ($this->hasError()) {
            if (isset($this->board[$piece])) {
                array_push($this->board[$piece], $tile);
            } else {
                $this->board[$piece] = [$tile];
            }
        } else {
            if (isset($this->board[$to])) {
                array_push($this->board[$to], $tile);
            } else {
                $this->board[$to] = [$tile];
            }

            if (count($this->board[$piece]) == 0) {
                unset($this->board[$piece]);
            }

            $this->setOtherPlayer();
            $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "move", ?, ?, ?, ?)');
            $stmt->bind_param('issis', $this->game_id, $piece, $to, $this->last_move, $this->serializeState());
            $stmt->execute();
            $this->last_move = $this->database->insert_id;
        }
    }

    public function pass() {
        $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "pass", null, null, ?, ?)');
        $stmt->bind_param('iis', $this->game_id, $this->last_move, $this->serializeState());
        $stmt->execute();
        $this->last_move = $this->database->insert_id;
        $this->setOtherPlayer();
    }

    public function undo() {
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE id = ?');
        $stmt->bind_param('i', $this->last_move);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_array();
        $this->last_move = $result[5];
        $this->loadState($result[6]);
    }

    public function restart() {
        $this->board = [];
        $this->hand = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3], 1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $this->current_player = 0;
        $this->last_move = 0;
        $stmt = $this->database->prepare('INSERT INTO games VALUES ()');
        $stmt->execute();
        $this->game_id = $this->database->insert_id;
    }
}