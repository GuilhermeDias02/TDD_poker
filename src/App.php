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

function findRule(array $community, array $playerHole): int {
    $all = array_merge($community, $playerHole);

    $rankOrder = [
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
        '10' => 10,
        'J' => 11,
        'Q' => 12,
        'K' => 13,
        'A' => 14
    ];

    $values = array_map(fn($c) => $rankOrder[$c['value']], $all);
    $suits  = array_map(fn($c) => $c['suit'], $all);

    // Count occurrences of each value
    $valueCounts = array_count_values($values);
    arsort($valueCounts);
    $counts = array_values($valueCounts);

    // Check flush: 5+ cards of the same suit
    $suitCounts = array_count_values($suits);
    $flushSuit = null;
    foreach ($suitCounts as $suit => $count) {
        if ($count >= 5) {
            $flushSuit = $suit;
            break;
        }
    }

    // Check straight: 5 consecutive ranks among unique values
    $uniqueRanks = array_unique($values);
    sort($uniqueRanks);
    $straightHigh = null;
    for ($i = count($uniqueRanks) - 1; $i >= 4; $i--) {
        if (
            $uniqueRanks[$i] - $uniqueRanks[$i - 4] === 4
            && count(array_unique(array_slice($uniqueRanks, $i - 4, 5))) === 5
        ) {
            $straightHigh = $uniqueRanks[$i];
            break;
        }
    }

    // 1 - Straight flush
    if ($flushSuit !== null && $straightHigh !== null) {
        $flushRanks = [];
        foreach ($all as $card) {
            if ($card['suit'] === $flushSuit) {
                $flushRanks[] = $rankOrder[$card['value']];
            }
        }
        sort($flushRanks);
        for ($i = count($flushRanks) - 1; $i >= 4; $i--) {
            if ($flushRanks[$i] - $flushRanks[$i - 4] === 4) {
                return 1;
            }
        }
    }

    // 2 - Four of a kind
    if ($counts[0] === 4) return 2;

    // 3 - Full house
    if ($counts[0] === 3 && $counts[1] >= 2) return 3;

    // 4 - Flush
    if ($flushSuit !== null) return 4;

    // 5 - Straight
    if ($straightHigh !== null) return 5;

    // 6 - Three of a kind
    if ($counts[0] === 3) return 6;

    // 7 - Two pair
    if ($counts[0] === 2 && $counts[1] === 2) return 7;

    // 8 - One pair
    if ($counts[0] === 2) return 8;

    // 9 - High card
    return 9;
}

function getWinner(array $playerRules): array {
    //inside an array return the number of the player with the highest rule inside
    //if tie explicit it by returning an array with multiple player numbers
}

echo "Generated cards:\n";
$result = generateCards(2);
print_r($result);

for ($i = 0; $i < 2; $i++) {
    echo "\n rule associated with player " . $i + 1 . " is " . findRule($result['community'], $result['holes'][$i]);
}
