<?php

namespace public;

// start session if not already started
use Chess\Game;
use src\GamePlay;

require(__DIR__.'/../vendor/autoload.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$game = $_SESSION['game'] ?? new Game(Game::VARIANT_CLASSICAL, Game::MODE_STOCKFISH);

$color = $_SESSION['color'] ?? 'w';

if (isset($_POST['move'])) {
    $game->play($color, $_POST['move']);
    $color = $color === 'w' ? 'b' : 'w';
    $ai = $game->ai(['Skill Level' => (int)$_SESSION['level']], ['depth' => (int)$_SESSION['depth']]);
    $game->play($color, $ai->move);
    $color = $color === 'w' ? 'b' : 'w';
}
if (isset($_GET['reset'])) {
    $game = new Game(Game::VARIANT_CLASSICAL, Game::MODE_STOCKFISH);
    $_SESSION['level'] = (int)($_GET['level'] ?? '1');
    $_SESSION['depth'] = (int)($_GET['depth'] ?? '15');
    $color = 'w';
}

$_SESSION['game'] = $game;
$_SESSION['color'] = $color;

// Display a html table with the board
// Symbols are utf by using Terminal::getUtf8ForPiece($piece)
// Data = $game->getBoard()->toAsciiArray(false)

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chess</title>
    <style>
        table {
            border-collapse: collapse;
        }
        table td {
            width: 50px;
            height: 50px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid lightseagreen;
        }
        table td.black {
            background-color: lightseagreen;
        }
        table td.white {
            background-color: #fff;
        }
        table td.black:hover {
            background-color: #333;
        }
        table td.white:hover {
            background-color: #ccc;
        }
        /* Font bigger */
        table td {
            font-size: 2.5em;
            cursor: default;
        }
    </style>
    <script>
        function doThis(i, j, piece) {
            // Add the move to the form
            // 14 = e2
            // 47 = g5
            // Convert.
            let a = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
            let b = ['1', '2', '3', '4', '5', '6', '7', '8'];
            // Convert i and j to a  position
            let pos = a[j] + b[i];

            let moveInput = document.getElementById('move');

            // If the field is empty, then add either the piece or if it is a pawn, then add the from position
            if (moveInput.value === '') {
                if (piece.search(/[prnbkqPRNBKQ]/) !== -1) {
                    console.log('Piece');

                    // if piece is pawn, add i to form
                    if (piece.search(/[pP]/) !== -1) {
                        moveInput.value = a[j];
                    } else {
                        moveInput.value = piece.toUpperCase();
                    }
                }
            } else {
                // If the field is not empty, then add the to position

                // If input length === 1, then it is a pawn. If piece is null, remove input. Else, add x
                // If input is lowercase, then it is a pawn.
                if (moveInput.value.length === 1 && moveInput.value === moveInput.value.toLowerCase()) {
                    if (piece === '.') {
                        moveInput.value = '';
                    } else {
                        moveInput.value += 'x';
                    }
                } else {
                    if (piece !== '.') {
                        moveInput.value += 'x';
                    }
                }

                moveInput.value += pos;
                document.getElementById('form').submit();
            }
        }
    </script>
</head>
<body>
    <table>
        <?php
        $board = $game->getBoard()->toAsciiArray(false);

        // each rank, each piece
        foreach ($board as $i => $rank) {
            echo '<tr>';
            foreach ($rank as $j => $piece) {
                $color = ($i + $j) % 2 === 0 ? 'white' : 'black';
                $ogPiece = trim($piece);
                $piece = $piece ? GamePlay::getUtf8ForPiece($piece) : '&nbsp;';
                echo "<td class='$color' onclick='doThis(\"$i\", \"$j\", \"$ogPiece\")'>";
                echo $piece;
                echo '</td>';
            }
            echo '</tr>';
        }

        $eval = round(GamePlay::eval($game), 2);
        ?>
    </table>
    <div>
        <span>Eval: </span>
        <span><?php echo $eval; ?></span>
    </div>
    <form action="?" method="post" id="form">
        <input type="text" name="move" id="move" placeholder="Move" autofocus>
        <input type="submit" value="Play">
    </form>
    <form action="" method="get">
        <input type="hidden" name="reset" value="1">
        <input type="number" name="level" min="0" max="20" value="<?=$_SESSION['level'] ?? 0?>">
        <input type="number" name="depth" min="1" max="15" value="<?=$_SESSION['depth'] ?? 15?>">
        <input type="submit" value="Reset">
    </form>
</body>
</html>
