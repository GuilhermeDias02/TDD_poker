<?php

require_once __DIR__ . '/../src/App.php';

test('generateCards returns 5 community cards and 2 hole cards for 2 players', function () {
    $nbPlayers = 2;
    $cards = generateCards($nbPlayers);
    $this->assertSame(5, count($cards["community"]));
    for ($i = 0; $i < $nbPlayers; $i++) {
        $this->assertSame(2, count($cards["holes"][$i]));
    }
});