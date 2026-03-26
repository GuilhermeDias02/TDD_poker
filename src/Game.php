<?php

require_once __DIR__ . '/ResultsInterface.php';

class Game
{
    private const SUITS = ['hearts', 'diamonds', 'clubs', 'spades'];

    private const VALUES = ['A', 'K', 'Q', 'J', '10', '9', '8', '7', '6', '5', '4', '3', '2'];

    private const RANK_ORDER = [
        '2' => 2,  '3' => 3,  '4' => 4,  '5' => 5,  '6' => 6,
        '7' => 7,  '8' => 8,  '9' => 9,  '10' => 10,
        'J' => 11, 'Q' => 12, 'K' => 13, 'A' => 14,
    ];

    // -------------------------------------------------------------------------
    // Card generation
    // -------------------------------------------------------------------------

    public function generateCards(int $players): array
    {
        $cards = [];
        foreach (self::SUITS as $suit) {
            foreach (self::VALUES as $value) {
                $cards[] = ['value' => $value, 'suit' => $suit];
            }
        }
        shuffle($cards);

        $community = array_slice($cards, 0, 5);
        $holes = [];
        for ($i = 0; $i < $players; $i++) {
            $holes[$i] = array_slice($cards, 5 + $i * 2, 2);
        }

        return ['community' => $community, 'holes' => $holes];
    }

    // -------------------------------------------------------------------------
    // Hand detection
    // -------------------------------------------------------------------------

    public function findRule(array $community, array $playerHole): int
    {
        $all    = array_merge($community, $playerHole);
        $values = array_map(fn($c) => self::RANK_ORDER[$c['value']], $all);
        $suits  = array_map(fn($c) => $c['suit'], $all);

        [$counts, $countKeys] = $this->countsByFrequency($values);
        $flushSuit            = $this->findFlushSuit($suits);
        $straightHigh         = $this->findStraightHigh($values);

        if ($flushSuit !== null && $this->findStraightFlushHigh($all, $flushSuit) !== null) {
            return 1;
        }
        if ($counts[0] === 4)                       return 2;
        if ($counts[0] === 3 && $counts[1] >= 2)    return 3;
        if ($flushSuit !== null)                    return 4;
        if ($straightHigh !== null)                 return 5;
        if ($counts[0] === 3)                       return 6;
        if ($counts[0] === 2 && $counts[1] === 2)   return 7;
        if ($counts[0] === 2)                       return 8;

        return 9;
    }

    // -------------------------------------------------------------------------
    // Scoring (hand rank + tiebreakers encoded as a single integer)
    // -------------------------------------------------------------------------

    public function findScore(array $community, array $playerHole): int
    {
        $all    = array_merge($community, $playerHole);
        $values = array_map(fn($c) => self::RANK_ORDER[$c['value']], $all);
        $suits  = array_map(fn($c) => $c['suit'], $all);

        [$counts, $countKeys] = $this->countsByFrequency($values);
        $flushSuit            = $this->findFlushSuit($suits);
        $straightHigh         = $this->findStraightHigh($values);
        $straightFlushHigh    = $flushSuit !== null
            ? $this->findStraightFlushHigh($all, $flushSuit)
            : null;

        $kickers = function(array $excludeRanks) use ($values): array {
            $k = array_filter($values, fn($v) => !in_array($v, $excludeRanks));
            rsort($k);
            return array_values($k);
        };

        // 1 - Straight flush
        if ($straightFlushHigh !== null) {
            return $this->encode(9, [$straightFlushHigh]);
        }

        // 2 - Four of a kind
        if ($counts[0] === 4) {
            $quad = $countKeys[0];
            $k    = $kickers([$quad]);
            return $this->encode(8, [$quad, $k[0] ?? 0]);
        }

        // 3 - Full house
        if ($counts[0] === 3 && $counts[1] >= 2) {
            return $this->encode(7, [$countKeys[0], $countKeys[1]]);
        }

        // 4 - Flush
        if ($flushSuit !== null) {
            $flushCards = [];
            foreach ($all as $card) {
                if ($card['suit'] === $flushSuit) {
                    $flushCards[] = self::RANK_ORDER[$card['value']];
                }
            }
            rsort($flushCards);
            return $this->encode(6, array_slice($flushCards, 0, 5));
        }

        // 5 - Straight
        if ($straightHigh !== null) {
            return $this->encode(5, [$straightHigh]);
        }

        // 6 - Three of a kind
        if ($counts[0] === 3) {
            $trip = $countKeys[0];
            $k    = $kickers([$trip]);
            return $this->encode(4, [$trip, $k[0] ?? 0, $k[1] ?? 0]);
        }

        // 7 - Two pair
        if ($counts[0] === 2 && $counts[1] === 2) {
            $hi = max($countKeys[0], $countKeys[1]);
            $lo = min($countKeys[0], $countKeys[1]);
            $k  = $kickers([$hi, $lo]);
            return $this->encode(3, [$hi, $lo, $k[0] ?? 0]);
        }

        // 8 - One pair
        if ($counts[0] === 2) {
            $pair = $countKeys[0];
            $k    = $kickers([$pair]);
            return $this->encode(2, [$pair, $k[0] ?? 0, $k[1] ?? 0, $k[2] ?? 0]);
        }

        // 9 - High card
        rsort($values);
        return $this->encode(1, array_slice($values, 0, 5));
    }

    // -------------------------------------------------------------------------
    // Winner resolution
    // -------------------------------------------------------------------------

    public function getWinner(array $community, array $holes): array
    {
        $scores = array_map(fn($hole) => $this->findScore($community, $hole), $holes);
        $best   = max($scores);
        return array_keys(array_filter($scores, fn($s) => $s === $best));
    }

    // -------------------------------------------------------------------------
    // Display
    // -------------------------------------------------------------------------

    public function run(int $players, ResultsInterface $results): void
    {
        $round = $this->generateCards($players);

        $results->showCards($round['community'], $round['holes']);

        for ($i = 0; $i < $players; $i++) {
            $rule = $this->findRule($round['community'], $round['holes'][$i]);
            $results->showPlayerRule($i, $rule);
        }

        $results->showWinner($this->getWinner($round['community'], $round['holes']));
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function countsByFrequency(array $values): array
    {
        $valueCounts = array_count_values($values);
        uksort($valueCounts, function($a, $b) use ($valueCounts) {
            if ($valueCounts[$b] !== $valueCounts[$a]) return $valueCounts[$b] - $valueCounts[$a];
            return $b - $a;
        });
        return [array_values($valueCounts), array_keys($valueCounts)];
    }

    private function findFlushSuit(array $suits): ?string
    {
        $suitCounts = array_count_values($suits);
        foreach ($suitCounts as $suit => $count) {
            if ($count >= 5) return $suit;
        }
        return null;
    }

    private function findStraightHigh(array $values): ?int
    {
        $unique = array_values(array_unique($values));
        sort($unique);
        for ($i = count($unique) - 1; $i >= 4; $i--) {
            if ($unique[$i] - $unique[$i - 4] === 4) return $unique[$i];
        }
        return null;
    }

    private function findStraightFlushHigh(array $all, string $flushSuit): ?int
    {
        $flushRanks = [];
        foreach ($all as $card) {
            if ($card['suit'] === $flushSuit) {
                $flushRanks[] = self::RANK_ORDER[$card['value']];
            }
        }
        sort($flushRanks);
        $flushRanks = array_values(array_unique($flushRanks));
        for ($i = count($flushRanks) - 1; $i >= 4; $i--) {
            if ($flushRanks[$i] - $flushRanks[$i - 4] === 4) return $flushRanks[$i];
        }
        return null;
    }

    private function encode(int $tier, array $tb): int
    {
        $tb    = array_slice(array_pad($tb, 5, 0), 0, 5);
        $score = $tier;
        foreach ($tb as $v) {
            $score = $score * 100 + $v;
        }
        return $score;
    }
}
