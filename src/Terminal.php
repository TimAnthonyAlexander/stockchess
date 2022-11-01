<?php

namespace src;

use Chess\Game;
use Chess\Piece\AbstractPiece;
use Chess\Piece\AsciiArray;
use Chess\Variant\Classical\PGN\AN\Color;

class Terminal
{
    public static function utf8(Game $game, bool $flipped = false): string
    {
        $string = '';
        $array = $game->getBoard()->toAsciiArray($flipped);

        $string .= $game->getBoard()->getMovetext().PHP_EOL;

        $string .= '+-----------------+' . PHP_EOL;
        foreach ($array as $i => $rank) {
            $string .= $i+1 . ' ';
            foreach ($rank as $j => $square) {
                $string .= self::getUtf8ForPiece($square) . ' ';
            }
            $string .= PHP_EOL;
        }
        $string .= '  a b c d e f g h'.PHP_EOL;
        $string .= '+-----------------+' . PHP_EOL;


        $totalEval = self::eval($game);

        $string .= 'Evaluation: '.$totalEval.PHP_EOL;

        return $string;
    }

    public static function getUtf8ForPiece(string $piece): string
    {
        $pieces = [
            'P' => '♙',
            'N' => '♘',
            'B' => '♗',
            'R' => '♖',
            'Q' => '♕',
            'K' => '♔',
            'p' => '♟',
            'n' => '♞',
            'b' => '♝',
            'r' => '♜',
            'q' => '♛',
            'k' => '♚',
        ];

        return $pieces[trim($piece)] ?? ' ';
    }

    /**
     * @param Game $game
     * @return float
     */
    public static function eval(Game $game): float
    {
        $defEval = $game->getBoard()->getDefenseEval();
        $spEval  = $game->getBoard()->getSpaceEval();

        $pieces = self::getPieces($game);

        $defEval = count($defEval->w) - count($defEval->b);
        $spEval  = count($spEval->w) - count($spEval->b);

        return ($defEval + $spEval) * 0.01 + $pieces;
    }

    public static function getPieces(Game $game): float
    {
        $eval = 0;
        $board = $game->getBoard();
        foreach ($board->getPieces() as $piece) {
            assert($piece instanceof AbstractPiece);
            $eval += self::getValue($piece->getId(), $piece->getColor());
        }

        return $eval;
    }

    public static function getValue(string $piece, string $color): float
    {
        $pieces = [
            'P' => 1,
            'N' => 3,
            'B' => 3,
            'R' => 5,
            'Q' => 9,
            'K' => 0,
        ];

        $value = $pieces[$piece] ?? 0;

        return $color === 'w' ? $value : -$value;
    }
}
