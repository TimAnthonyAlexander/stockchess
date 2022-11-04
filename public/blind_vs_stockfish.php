<?php

namespace public;

use Chess\Game;
use Chess\Variant\Classical\Board;
use src\GamePlay;

require(__DIR__.'/../vendor/autoload.php');

$game = new Game(Game::VARIANT_CLASSICAL, Game::MODE_STOCKFISH);

$color = 'w';

while(true) {
    print str_repeat('-', 35) . PHP_EOL;

    if ($color === 'b') {
        $ai = $game->ai(['Skill Level' => 20], ['depth' => 15]);
        $game->play($color, $ai->move);
    } else {
        print "Move: ";
        $playerMove = trim(fgets(STDIN));
        $game->play($color, $playerMove);
    }

    print $game->getBoard()->getMovetext() . PHP_EOL;
    $color = $color === 'w' ? 'b' : 'w';
}

