<?php

namespace public;

// start session if not already started
use Chess\Exception\BoardException;
use Chess\Game;
use src\GamePlay;
use src\TimFish;

require(__DIR__.'/../vendor/autoload.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['level'])) {
    $_SESSION['level'] = 0;
}
if (!isset($_SESSION['depth'])) {
    $_SESSION['depth'] = 1;
}
if (!isset($_SESSION['showbest'])) {
    $_SESSION['showbest'] = false;
}

$bestMove = '';

$game = $_SESSION['game'] ?? new Game(Game::VARIANT_CLASSICAL, Game::MODE_STOCKFISH);
assert($game instanceof Game);

$color = $_SESSION['color'] ?? 'w';

$check = false;
$mate = false;
$stalemate = false;

$_SESSION['lastMove'] = $_SESSION['lastMove'] ?? 'start';

$level = (int)($_SESSION['level'] ?? 0);

$engineLevel = match(true) {
    $level < 3 => ' TimFish Beginner ',
    $level < 6 => ' TimFish Novice ',
    $level < 9 => ' TimFish Amateur ',
    $level < 12 => ' TimFish Professional ',
    $level < 15 => ' TimFish Master ',
    $level < 18 => ' TimFish Grandmaster ',
    $level < 21 => ' TimFish Super Grandmaster ',
    default => 'TimFish Random ',
};

$multiplier = 88;
$randomness = (22 - $level);
$isRandom = random_int(0, $multiplier) < $randomness;

$randomnessString = sprintf(' (%s%%)', round($randomness/$multiplier, 2) * 100);

if ($isRandom) {
    $engineLevel = '<span style="color: red;">'.$engineLevel.'</span>';
} else {
    $engineLevel = '<span style="color: green;">'.$engineLevel.'</span>';
}

if (isset($_GET['aimove'])) {
    print <<<HTML
<script>
// Remove the ?aimove=1 from the URL
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href.split("?")[0]);
}
</script>
HTML;

    try {
        if ($level >= 0) {
            if (!$isRandom) {
                $ai = $game->ai(['Skill Level' => $level], ['depth' => (int)$_SESSION['depth']]);
                $game->play($color, $ai->move);
                $_SESSION['lastMove'] = $ai->move;
                $color                = $color === 'w' ? 'b' : 'w';
            } else {
                $moves = $game->getBoard()->legalMoves();
                $move  = $moves[random_int(0, count($moves) - 1)];
                $game->play($color, $move);
                $_SESSION['lastMove'] = $move;
                $color                = $color === 'w' ? 'b' : 'w';
            }
        }

        if ($game->getBoard()->isCheck()) {
            $check = true;
        }
        if ($game->getBoard()->isMate()) {
            $mate = true;
        }
        if ($game->getBoard()->isStalemate()) {
            $stalemate = true;
        }
    } catch (BoardException) {
        print "Invalid move<br>";
    }
    $betterAi = $game->ai(['Skill Level' => min(20, $level+1)], ['depth' => min(15, (int)$_SESSION['depth']+3)]);
    $bestMove = $betterAi->move;
}

if ($level === -1) {
    $engineLevel = 'OTB Human ';
    $randomnessString = '';
}

if (isset($_POST['move'])) {
    try {
        $played = $game->play($color, $_POST['move']);

        if ($played) {
            $color = $color === 'w' ? 'b' : 'w';
            $level = (int)$_SESSION['level'];

            $_SESSION['lastMove'] = $_POST['move'];

            if ($level >= 0) {
                $get           = $_GET;
                $get['aimove'] = true;
                $getQuery      = http_build_query($get);
                print <<<HTML
<script>
    setTimeout(function() {
        window.location.href = window.location.pathname + '?' + '$getQuery';
    }, 1000);
</script>
HTML;
            }


            if ($game->getBoard()->isCheck()) {
                $check = true;
            }
            if ($game->getBoard()->isMate()) {
                $mate = true;
            }
            if ($game->getBoard()->isStalemate()) {
                $stalemate = true;
            }
        }
    } catch (BoardException) {
        print "Invalid move<br>";
    }
}

if (isset($_GET['reset'])) {
    $_SESSION['level'] = (int)(($_GET['level'] ?? '1') / 100) - 1;
    $_SESSION['depth'] = (int)($_GET['depth'] ?? '15');
    $_SESSION['showbest'] = (bool)($_GET['showbest'] ?? false);
    $_SESSION['variant'] = $_GET['variant'] ?? 'classical';

    $variant = $_SESSION['variant'] === 'classical' ? Game::VARIANT_CLASSICAL : Game::VARIANT_960;

    $game = new Game($variant, Game::MODE_STOCKFISH);
    $color = 'w';

    $_SESSION['lastMove'] = 'start';

    $randomness = (22 - $_SESSION['level']);

    $randomnessString = sprintf(' (%s%%)', round($randomness/$multiplier, 2) * 100);

    print <<<HTML
<script>
// Remove the ?reset=1 from the URL
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href.split("?")[0]);
}

document.location.reload();
</script>
HTML;

}
if (isset($_GET['undo'])) {
    $game->getBoard()->undo();
    $game->getBoard()->undo();
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
    <script src="script.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Centered table -->
<div style="width: 100%; text-align: center;">
    <h1>Chess by Tim</h1>
    <label>Last move:
        <input type="text" value="<?= ($_SESSION['lastMove'] ?? '') ?>" disabled>
    </label><br><?= $engineLevel . (($level+1)*100) . $randomnessString ?><br>
    <?php
    if ($_SESSION['showbest']) {
        print <<<HTML
<label>Best move for you:
    <input type="text" value="$bestMove" disabled>
</label><br>
HTML;
    }
    if ($check) {
        print "<h2>Check!</h2>";
    }
    if ($mate) {
        print "<h2>Mate!</h2>";
    }
    if ($stalemate) {
        print "<h2>Stalemate!</h2>";
    }
    ?>
    <hr>
    <table style="margin-left: auto; margin-right: auto;">
        <?php
        $board = $game->getBoard()->toAsciiArray(false);

        // each rank, each piece
        foreach ($board as $i => $rank) {
            echo '<tr>';
            $isFirst = true;
            foreach ($rank as $j => $piece) {
                $rank = $i+1;
                $file = chr($j+97);
                $color = ($i + $j) % 2 === 0 ? 'white' : 'black';
                $lastMove = substr($_SESSION['lastMove'], -2);
                if ($lastMove === $file.$rank) {
                    $color = 'last';
                }
                $id = uniqid('', true);
                $ogPiece = trim($piece);
                $piece = $piece ? GamePlay::getUtf8ForPiece($piece) : '&nbsp;';
                echo $isFirst ? '<td style="border: none; font-size: 15px;">'.($i + 1).'</td>' : '';
                echo "<td class='$color' id='$id' onclick='doThis(\"$i\", \"$j\", \"$ogPiece\", \"$id\")'>";
                echo $piece;
                echo '</td>';
                $isFirst = false;
            }

            echo '</tr>';
        }

        echo '<td style="border: none;"></td>';

        for ($i = 0; $i < 8; $i++) {
            echo '<td style="border: none; font-size: 15px;">'.chr(97 + $i).'</td>';
        }

        $eval = round(GamePlay::eval($game), 2);

        #$bestMove = TimFish::findBestMove($game);
        #print '<h5>TimFish: ' . $bestMove[1] . '</h5>';
        ?>
    </table>
    <div>
        <div>
            <span>Eval: </span>
            <span><?php echo $eval; ?></span>
        </div>
        <form action="?" method="post" id="form">
            <input type="text" name="move" id="move">
            <input type="submit" value="Play" hidden>
        </form>
        <form action="" method="get">
            <input type="hidden" name="undo" value="1">
            <input type="submit" value="Undo">
        </form>
        <p><?= $game->getBoard()->getMovetext() ?></p>
        <hr>
        <form action="" method="get">
            <input type="hidden" name="reset" value="1">
            <label>AI Rating: [100-2100 | 0 deactivates engine]<br>
                <input type="number" name="level" min="0" max="2100" value="<?=(($_SESSION['level'] ?? 1)+1)*100?>">
            </label><br>
            <label>Depth: [1-15]<br>
                <input type="number" name="depth" min="1" max="15" value="<?=$_SESSION['depth'] ?? 15?>">
            </label><br>
            <label>Show best move<br>
                <select name="showbest">
                    <option value="0" <?= $_SESSION['showbest'] === false ? 'selected' : ''?>>No</option>
                    <option value="1" <?= $_SESSION['showbest'] ? 'selected' : ''?>>Yes</option>
                </select>
            </label><br>
            <label>Variant<br>
                <select name="variant">
                    <option value="classical" <?= $_SESSION['variant'] === 'classical' ? 'selected' : ''?>>Standard</option>
                    <option value="chess960" <?= $_SESSION['variant'] === 'chess960' ? 'selected' : ''?>>Chess960</option>
                </select>
            </label><br>
            <input type="submit" value="Reset">
        </form>
    </div>
</div>
</body>
</html>
