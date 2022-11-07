<?php

namespace src;

use Chess\Eval\AbsoluteForkEval;
use Chess\Eval\AbsolutePinEval;
use Chess\Eval\AttackEval;
use Chess\Eval\BadBishopEval;
use Chess\Eval\KingSafetyEval;
use Chess\Eval\TacticsEval;
use Chess\Game;
use Chess\Piece\AbstractPiece;

class GamePlay
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

        $absoluteForkEval = new AbsoluteForkEval($game->getBoard());
        $forkEvaluation = $absoluteForkEval->eval();

        $absolutePinEval = new AbsolutePinEval($game->getBoard());
        $pinEvaluation = $absolutePinEval->eval();

        $badBishopEval = new BadBishopEval($game->getBoard());
        $badBishopEvaluation = $badBishopEval->eval();

        $kingSafetyEval = new KingSafetyEval($game->getBoard());
        $kingSafetyEvaluation = $kingSafetyEval->eval();

        $attackEval = new AttackEval($game->getBoard());
        $attackEvaluation = $attackEval->eval();

        $tacticsEval = new TacticsEval($game->getBoard());
        $tacticsEvaluation = $tacticsEval->eval();

        $pieces = self::getPieces($game);

        $defEval = count($defEval->w) - count($defEval->b);
        $spEval  = count($spEval->w) - count($spEval->b);
        $forkEval = $forkEvaluation['w'] - $forkEvaluation['b'];
        $pinEval = $pinEvaluation['w'] - $pinEvaluation['b'];
        $badBishopEval = $badBishopEvaluation['w'] - $badBishopEvaluation['b'];
        $kingSafetyEval = ($kingSafetyEvaluation['w'] - $kingSafetyEvaluation['b']) * 1.25;
        $attackEval = $attackEvaluation['w'] - $attackEvaluation['b'];
        $tacticsEval = $tacticsEvaluation['w'] - $tacticsEvaluation['b'];

        return ($defEval + $spEval + $forkEval + $pinEval + $badBishopEval + $kingSafetyEval + $attackEval + $tacticsEval) * 0.01 + $pieces;
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
