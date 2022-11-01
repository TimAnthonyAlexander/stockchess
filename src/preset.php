<?php

namespace src;

use PChess\Chess\Chess;
use PChess\Chess\Output\UnicodeOutput;

require(__DIR__.'/../vendor/autoload.php');

$chess = new Chess();
$unicode = new UnicodeOutput();

$moves = [
    'e4',
    'e5',
    'Qh5',
    'Qg5',
    'h3',
    'Qxh5',
    'Be2',
    'Qxe2+',
    'Kxe2',
];

foreach ($moves as $move) {
    FileCache::load();
    $eval = new Evaluation();
    #Evaluation::reset();
    //[$evalScore, $bestMove] = $eval->bestMoveAndEval($chess, 1);
    #$evalReturn = round($evalScore, 1);
    $chess->move($move);

    echo $unicode->render($chess) . PHP_EOL;

    $float = round($eval->evaluateBoard($chess), 4);

    print "Last move: $move" . PHP_EOL;
    #print "Best move was: $bestMove" . PHP_EOL;
    #print "Eval before: $evalReturn".PHP_EOL;
    Evaluation::reset();
    $newBestMoveAndReturn = $eval->bestMoveAndEval($chess, 4, 4);
    $newEvalScore = $newBestMoveAndReturn[0];
    $newEvalReturn = round($newEvalScore, 1);
    $newBestMove = $newBestMoveAndReturn[1];
    print "Eval: $newEvalReturn".PHP_EOL;
    print "Best move is: $newBestMove" . PHP_EOL;
    print "Piece Eval: $float".PHP_EOL;
    print "---------------------------------------".PHP_EOL;
    FileCache::save();
}
