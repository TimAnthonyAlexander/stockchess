<?php

namespace public;

use Chess\Game;
use Chess\Variant\Classical\Board;
use src\GamePlay;

require(__DIR__.'/../vendor/autoload.php');

$arg1 = $argv[1] ?? null;
$arg2 = $argv[2] ?? null;
$arg3 = $argv[3] ?? null;
$arg4 = $argv[4] ?? null;

$level1 = $arg1 ? (int)$arg1 : 20;
$level2 = $arg2 ? (int)$arg2 : 20;
$depth1 = $arg3 ? (int)$arg3 : 15;
$depth2 = $arg4 ? (int)$arg4 : 15;

$game = new Game(Game::VARIANT_CLASSICAL, Game::MODE_STOCKFISH);

$color = 'w';

/**
 * @param Game $game
 * @param int $level
 * @param string $color
 * @param int $depth
 * @return array
 */
function first(Game $game, int $level, string $color, int $depth): array
{
    print GamePlay::utf8($game);
    print str_repeat('-', 35) . PHP_EOL;

    $ai = $game->ai(['Skill Level' => $level], ['depth' => $depth]);
    $game->play($color, $ai?->move);

    $color = $color === 'w' ? 'b' : 'w';
    return array($ai, $color);
}

while(true) {
    [,$color] = first($game, $level1, $color, $depth1);

    $check = $game->getBoard()->isCheck();
    $mate = $game->getBoard()->isMate();
    $staleMate = $game->getBoard()->isStalemate();

    if ($check) {
        print "Check!" . PHP_EOL;
    }

    if ($mate) {
        print "Checkmate!" . PHP_EOL;
        break;
    }

    if ($staleMate) {
        print "Stalemate!" . PHP_EOL;
        break;
    }

    [, $color] = first($game, $level2, $color, $depth2);

    $check = $game->getBoard()->isCheck();
    $mate = $game->getBoard()->isMate();
    $staleMate = $game->getBoard()->isStalemate();

    if ($check) {
        print "Check!" . PHP_EOL;
    }

    if ($mate) {
        print "Checkmate!" . PHP_EOL;
        break;
    }

    if ($staleMate) {
        print "Stalemate!" . PHP_EOL;
        break;
    }

    print PHP_EOL. PHP_EOL;
}

