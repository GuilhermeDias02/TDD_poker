<?php

require_once __DIR__ . '/Game.php';
require_once __DIR__ . '/ConsoleResults.php';

$game = new Game();
$game->run(5, new ConsoleResults());
