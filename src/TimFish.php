<?php

namespace src;

use Chess\Game;
use Chess\Variant\Classical\Board;

ini_set('max_execution_time', 60);

class TimFish
{
    public static function findBestMove(Game $game, int $currentDepth = 0, int $depth = 4): array
    {
        $board = clone $game->getBoard();

        $turn = $board->getTurn();

        $moves = $board->legalMoves();

        $eval = $turn === 'w' ? -9999 : 9999;

        $bestMove = null;

        $maxTime = ($depth - $currentDepth) / $depth * 10;
        $time = microtime(true);

        foreach ($moves as $move) {
            $board->play($turn, $move);
            if ($currentDepth === $depth || microtime(true) - $time > $maxTime) {
                $currentEval = GamePlay::evalBoard($board, GamePlay::getPieces($game));
                $board->undo();
            } else {
                $currentEval = self::findBestMove($game, $currentDepth+1, $depth)[0];
            }
            if ($turn === 'w') {
                if ($currentEval > $eval) {
                    $bestMove = $move;
                    $eval = $currentEval;
                }
            } else {
                if ($currentEval < $eval) {
                    $bestMove = $move;
                    $eval = $currentEval;
                }
            }
            $board->undo();
        }

        return [$eval, $bestMove];
    }
}
