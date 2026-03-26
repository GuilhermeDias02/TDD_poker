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

function findScore(array $community, array $playerHole): int {
    $all = array_merge($community, $playerHole);

    $rankOrder = ['2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,
                  '9'=>9,'10'=>10,'J'=>11,'Q'=>12,'K'=>13,'A'=>14];

    $values = array_map(fn($c) => $rankOrder[$c['value']], $all);
    $suits  = array_map(fn($c) => $c['suit'], $all);

    // Value frequency map, sorted by count desc then rank desc
    $valueCounts = array_count_values($values);
    arsort($valueCounts);
    $counts = array_values($valueCounts);
    $countKeys = array_keys($valueCounts); // ranks ordered by count desc, then insertion

    // Sort countKeys so equal counts are broken by rank desc
    uksort($valueCounts, function($a, $b) use ($valueCounts) {
        if ($valueCounts[$b] !== $valueCounts[$a]) return $valueCounts[$b] - $valueCounts[$a];
        return $b - $a;
    });
    $countKeys = array_keys($valueCounts);
    $counts    = array_values($valueCounts);

    // Flush suit
    $suitCounts = array_count_values($suits);
    $flushSuit  = null;
    foreach ($suitCounts as $suit => $count) {
        if ($count >= 5) { $flushSuit = $suit; break; }
    }

    // Best straight high card among unique ranks
    $uniqueRanks = array_values(array_unique($values));
    sort($uniqueRanks);
    $straightHigh = null;
    for ($i = count($uniqueRanks) - 1; $i >= 4; $i--) {
        if ($uniqueRanks[$i] - $uniqueRanks[$i - 4] === 4) {
            $straightHigh = $uniqueRanks[$i];
            break;
        }
    }

    // Best straight flush high card
    $straightFlushHigh = null;
    if ($flushSuit !== null) {
        $flushRanks = [];
        foreach ($all as $card) {
            if ($card['suit'] === $flushSuit) $flushRanks[] = $rankOrder[$card['value']];
        }
        sort($flushRanks);
        $flushRanks = array_values(array_unique($flushRanks));
        for ($i = count($flushRanks) - 1; $i >= 4; $i--) {
            if ($flushRanks[$i] - $flushRanks[$i - 4] === 4) {
                $straightFlushHigh = $flushRanks[$i];
                break;
            }
        }
    }

    // Build an ordered tiebreak list [v1, v2, v3, v4, v5] for the best 5-card hand,
    // then encode as a single integer:
    // score = handTier * 100^5 + v1*100^4 + v2*100^3 + v3*100^2 + v4*100 + v5
    // handTier = 9 - handRank  (so higher tier = better hand)

    $encode = function(int $tier, array $tb): int {
        $tb = array_slice(array_pad($tb, 5, 0), 0, 5);
        $score = $tier;
        foreach ($tb as $v) {
            $score = $score * 100 + $v;
        }
        return $score;
    };

    // Helper: kickers = all card ranks not part of the primary combination, desc
    $kickers = function(array $excludeRanks) use ($values): array {
        $k = array_filter($values, fn($v) => !in_array($v, $excludeRanks));
        rsort($k);
        return array_values($k);
    };

    // 1 - Straight flush
    if ($straightFlushHigh !== null) {
        return $encode(9, [$straightFlushHigh]);
    }

    // 2 - Four of a kind
    if ($counts[0] === 4) {
        $quad = $countKeys[0];
        $k    = $kickers([$quad]);
        return $encode(8, [$quad, $k[0] ?? 0]);
    }

    // 3 - Full house: pick highest three-of-a-kind, then highest pair
    if ($counts[0] === 3 && $counts[1] >= 2) {
        $trip = $countKeys[0];
        $pair = $countKeys[1];
        return $encode(7, [$trip, $pair]);
    }

    // 4 - Flush: top 5 flush cards desc
    if ($flushSuit !== null) {
        $flushCards = [];
        foreach ($all as $card) {
            if ($card['suit'] === $flushSuit) $flushCards[] = $rankOrder[$card['value']];
        }
        rsort($flushCards);
        return $encode(6, array_slice($flushCards, 0, 5));
    }

    // 5 - Straight: high card of best straight
    if ($straightHigh !== null) {
        return $encode(5, [$straightHigh]);
    }

    // 6 - Three of a kind
    if ($counts[0] === 3) {
        $trip = $countKeys[0];
        $k    = $kickers([$trip]);
        return $encode(4, [$trip, $k[0] ?? 0, $k[1] ?? 0]);
    }

    // 7 - Two pair: higher pair, lower pair, kicker
    if ($counts[0] === 2 && $counts[1] === 2) {
        $hi = max($countKeys[0], $countKeys[1]);
        $lo = min($countKeys[0], $countKeys[1]);
        $k  = $kickers([$hi, $lo]);
        return $encode(3, [$hi, $lo, $k[0] ?? 0]);
    }

    // 8 - One pair: pair rank, then 3 kickers desc
    if ($counts[0] === 2) {
        $pair = $countKeys[0];
        $k    = $kickers([$pair]);
        return $encode(2, [$pair, $k[0] ?? 0, $k[1] ?? 0, $k[2] ?? 0]);
    }

    // 9 - High card: top 5 cards desc
    rsort($values);
    return $encode(1, array_slice($values, 0, 5));
}

function getWinner(array $community, array $holes): array {
    $scores = array_map(fn($hole) => findScore($community, $hole), $holes);
    $best   = max($scores);
    return array_keys(array_filter($scores, fn($s) => $s === $best));
}

function showWinnerOrTie(array $roundResults): void {
    if (count($roundResults) === 1) {
        echo "\nThe winner is player " . $roundResults[0] + 1 . " !";
    } else {
        echo "\nIt's a tie between players: ";
        $displayResults = array_map(function($val) { return $val + 1; }, $roundResults);
        echo implode(', ', $displayResults) . " !";
    }
}

echo "Generated cards:\n";
$result = generateCards(5);
print_r($result);

for ($i = 0; $i < 5; $i++) {
    echo "\nRule associated with player " . $i + 1 . " is " . findRule($result['community'], $result['holes'][$i]);
}

showWinnerOrTie(getWinner($result['community'], $result['holes']));
