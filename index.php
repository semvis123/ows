<?php
    session_start();

    include_once 'util.php';
    include_once 'game.php';
    include_once 'database.php';

    $db = getDatabase();
    
    $game = new Game($db);
    try {
        $game->loadFromSession();
    } catch (Exception $e) {
        header('Location: restart.php');
        exit(0);
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Hive</title>
        <style>
            div.board {
                width: 60%;
                height: 100%;
                min-height: 500px;
                float: left;
                overflow: scroll;
                position: relative;
            }

            div.board div.tile {
                position: absolute;
            }

            div.tile {
                display: inline-block;
                width: 4em;
                height: 4em;
                border: 1px solid black;
                box-sizing: border-box;
                font-size: 50%;
                padding: 2px;
            }

            div.tile span {
                display: block;
                width: 100%;
                text-align: center;
                font-size: 200%;
            }

            div.player0 {
                color: black;
                background: white;
            }

            div.player1 {
                color: white;
                background: black
            }

            div.stacked {
                border-width: 3px;
                border-color: red;
                padding: 0;
            }
        </style>
    </head>
    <body>
        <div class="board">
            <?php
                echo $game->getBoardHtml();
            ?>
        </div>

        <?php
            for ($player = 0; $player < 2; $player++) {
                echo "<div class=\"hand\">";
                echo $game->getPlayerName($player).": ";
                echo $game->getHandHtml($player);
                echo "</div>";
            }
        ?>

        <div class="turn">
            Turn: <?php echo $game->getPlayerName($game->getCurrentPlayer()); ?>
        </div>
        <form method="post" action="play.php">
            <select name="piece">
                <?php
                    foreach ($game->getHand($game->getCurrentPlayer()) as $tile => $ct) {
                        echo "<option value=\"$tile\">$tile</option>";
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    foreach ($game->getPossiblePlaces() as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <input type="submit" value="Play">
        </form>
        <form method="post" action="move.php">
            <select name="from">
                <?php
                    foreach ($game->getMovableTiles() as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    foreach ($game->getPossibleMoves() as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <input type="submit" value="Move">
        </form>
        <form method="post" action="pass.php">
            <input type="submit" value="Pass">
        </form>
        <form method="post" action="restart.php">
            <input type="submit" value="Restart">
        </form>
        <strong><?php 
            if ($game->hasError()) {
                echo $game->getError();
                $game->clearError();
            }
        ?></strong>
        <strong><?php 
            $winner = $game->isGameOver();
            if ($winner !== false && $winner != 2) {
                echo "Game over! ".$game->getPlayerName($winner)." wins!";
            } elseif ($winner === 2) {
                echo "Game over! It's a draw!";
            }
        ?></strong>
        <ol>
            <?php
                echo $game->getMoveHistoryHtml();
            ?>
        </ol>
        <form method="post" action="undo.php">
            <input type="submit" value="Undo">
        </form>
    </body>
</html>

