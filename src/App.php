<?php

function generateCards(int $players): array {
    $SUITS = [
        'hearts',
        'diamonds',
        'clubs',
        'spades',
    ];

    $VALUES = [
        'A',
        'K',
        'Q',
        'J',
        '10',
        '9',
        '8',
        '7',
        '6',
        '5',
        '4',
        '3',
        '2',
    ];

    $cards = [];
    foreach ($SUITS as $suit) {
        foreach ($VALUES as $value) {
            $cards[] = ["value" => $value, "suit" => $suit];
        }
    }
    shuffle($cards);

    $community = array_slice($cards, 0, 5);
    $holes = [];
    for ($i = 0; $i < $players; $i++) {
        $holes[$i] = array_slice($cards, 5 + $i * 2, 2);
    }
    $result = ["community" => $community, "holes" => $holes];
    return $result;
}

function findRule(array $community, array $playerHole): bool{
    
    return false;
}

echo "Generated cards:/n";
print_r(generateCards(2));
