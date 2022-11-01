<?php

namespace src;

use PChess\Chess\Chess;
use PChess\Chess\Output\UnicodeOutput;

require(__DIR__.'/../vendor/autoload.php');

$unicode = new UnicodeOutput();

$chess = new Chess();
while (!$chess->gameOver()) {
    $eval = new Evaluation();
    [$evalScore, $bestMove] = $eval->bestMoveAndEval($chess, 5, 5);
    $evalReturn = round($evalScore, 1);
    $chess->move($bestMove);

    echo $unicode->render($chess) . PHP_EOL;

    print "Last move: $bestMove" . PHP_EOL;
    print "Eval: $evalReturn".PHP_EOL;
    print "--------------------------------".PHP_EOL;
}

