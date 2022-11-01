<?php

namespace src;

use PChess\Chess\Chess;
use PChess\Chess\Piece;

class Evaluation
{
    public static function reset(): void
    {
        $GLOBALS['timer'] = microtime(true);
    }

    public function bestMoveAndEval(Chess $chess, int $depth, int $maxDepth): array
    {
        $cacheName = InstantCache::generateName($chess->board, $depth, 'bestMoveAndEval');

        if (InstantCache::isset($cacheName)) {
            return InstantCache::get($cacheName);
        }
        if (FileCache::isset($cacheName)) {
            return FileCache::get($cacheName);
        }

        $chessClone = clone $chess;

        $moves = $chessClone->moves();
        $originalCount = count($moves);
        $moves = array_slice($moves, 0, (int)($originalCount * (($depth+1) / ($maxDepth+1))));

        if ($depth === 0 || empty($moves) || $GLOBALS['timer'] < microtime(true) - 5) {
            $evaluation = $this->evaluateBoard($chess);
            InstantCache::set($cacheName, [$evaluation, null]);
            FileCache::set($cacheName, [$evaluation, null]);
            return [$evaluation, null];
        }

        $bestScore = -999999;

        foreach ($moves as $move) {
            $chessClone->move($move);
            $bestMoveAndEval = $this->bestMoveAndEval($chessClone, $depth-1, $maxDepth);
            $score = -$bestMoveAndEval[0];
            $bestMoveAfter = $bestMoveAndEval[1];
            $chessClone->undo();

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMove = $move;
            }
        }

        InstantCache::set($cacheName, [$bestScore, $bestMove?->san ?? null]);
        FileCache::set($cacheName, [$bestScore, $bestMove?->san ?? null]);
        return [$bestScore, $bestMove?->san ?? null];
    }

    private function getPieceValue(string $pieceType, string $color): float
    {
        $pieceValue = match ($pieceType) {
            Piece::PAWN => 1,
            Piece::KNIGHT => 3.2,
            Piece::BISHOP => 3.3,
            Piece::ROOK => 5,
            Piece::QUEEN => 9,
            Piece::KING => 200,
            default => 0,
        };

        return $color === Piece::WHITE ? $pieceValue : -$pieceValue;
    }

    public function evaluateBoard(Chess $chess): float
    {
        $cacheName = InstantCache::generateName($chess->board, 'board');

        if (InstantCache::isset($cacheName)) {
            return InstantCache::get($cacheName);
        }
        if (FileCache::isset($cacheName)) {
            return FileCache::get($cacheName);
        }

        $score = 0;

        $board = $chess->board;

        foreach ($board as $square) {
            $piece = $square;
            if ($piece === null) {
                continue;
            }

            assert($piece instanceof Piece);

            $color = $piece->getColor(); /* w, b */
            $pieceType = $piece->getType(); /* p, n, b, r, q, k */

            $score += $this->getPieceValue($pieceType, $color);
        }


        $inCheck = $chess->inCheck();
        $inDraw = $chess->inDraw();
        $inMate = $chess->inCheckmate();
        $inStalemate = $chess->inStalemate();

        if ($inCheck) {
            $score -= .5;
        }

        if ($inDraw) {
            $score = 0;
        }

        if ($inMate) {
            $score = -999999;
        }

        if ($inStalemate) {
            $score = 0;
        }


        InstantCache::set($cacheName, $score);
        FileCache::set($cacheName, $score);

        return $score;
    }


}
