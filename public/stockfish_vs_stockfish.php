<?php

namespace public;

use Chess\Game;
use Chess\Variant\Classical\Board;
use src\GamePlay;

require(__DIR__.'/../vendor/autoload.php');

$game = new Game(Game::VARIANT_CLASSICAL, Game::MODE_STOCKFISH);

$color = 'w';

while(true) {
    print GamePlay::utf8($game);
    print str_repeat('-', 35) . PHP_EOL;

    $ai = $game->ai(['Skill Level' => 20], ['depth' => 15]);
    $game->play($color, $ai->move);

    $color = $color === 'w' ? 'b' : 'w';
}

