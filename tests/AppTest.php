<?php

require_once __DIR__ . '/../src/App.php';

test('generateCards returns 5 community cards and 2 hole cards for 2 players', function () {
    $nbPlayers = 2;
    $cards = generateCards($nbPlayers);
    expect(count($cards["community"]))->toBe(5);
    for ($i = 0; $i < $nbPlayers; $i++) {
        expect(count($cards["holes"][$i]))->toBe(2);
    }
});

// 1 - Straight flush: A-K-Q-J-10 all hearts
test('rules returns 1 for straight flush', function () {
    $community = [
        ["value" => "A",  "suit" => "hearts"],
        ["value" => "K",  "suit" => "hearts"],
        ["value" => "Q",  "suit" => "hearts"],
        ["value" => "J",  "suit" => "hearts"],
        ["value" => "2",  "suit" => "clubs"],
    ];
    $playerHole = [
        ["value" => "10", "suit" => "hearts"],
        ["value" => "3",  "suit" => "diamonds"],
    ];
    expect(findRule($community, $playerHole))->toBe(1);
});

// 2 - Four of a kind: four Aces
test('rules returns 2 for four of a kind', function () {
    $community = [
        ["value" => "A", "suit" => "hearts"],
        ["value" => "A", "suit" => "diamonds"],
        ["value" => "A", "suit" => "clubs"],
        ["value" => "K", "suit" => "spades"],
        ["value" => "2", "suit" => "clubs"],
    ];
    $playerHole = [
        ["value" => "A", "suit" => "spades"],
        ["value" => "3", "suit" => "hearts"],
    ];
    expect(findRule($community, $playerHole))->toBe(2);
});

// 3 - Full house: three Kings + two Queens
test('rules returns 3 for full house', function () {
    $community = [
        ["value" => "K", "suit" => "hearts"],
        ["value" => "K", "suit" => "diamonds"],
        ["value" => "Q", "suit" => "clubs"],
        ["value" => "Q", "suit" => "spades"],
        ["value" => "2", "suit" => "clubs"],
    ];
    $playerHole = [
        ["value" => "K", "suit" => "clubs"],
        ["value" => "3", "suit" => "hearts"],
    ];
    expect(findRule($community, $playerHole))->toBe(3);
});

// 4 - Flush: five spades, non-sequential (not a straight flush)
test('rules returns 4 for flush', function () {
    $community = [
        ["value" => "A", "suit" => "spades"],
        ["value" => "J", "suit" => "spades"],
        ["value" => "8", "suit" => "spades"],
        ["value" => "5", "suit" => "spades"],
        ["value" => "K", "suit" => "hearts"],
    ];
    $playerHole = [
        ["value" => "2", "suit" => "spades"],
        ["value" => "3", "suit" => "clubs"],
    ];
    expect(findRule($community, $playerHole))->toBe(4);
});

// 5 - Straight: 5-6-7-8-9 mixed suits (not a flush)
test('rules returns 5 for straight', function () {
    $community = [
        ["value" => "5", "suit" => "hearts"],
        ["value" => "6", "suit" => "diamonds"],
        ["value" => "7", "suit" => "clubs"],
        ["value" => "8", "suit" => "spades"],
        ["value" => "K", "suit" => "hearts"],
    ];
    $playerHole = [
        ["value" => "9", "suit" => "hearts"],
        ["value" => "2", "suit" => "clubs"],
    ];
    expect(findRule($community, $playerHole))->toBe(5);
});

// 6 - Three of a kind: three Jacks, no pair among remaining cards
test('rules returns 6 for three of a kind', function () {
    $community = [
        ["value" => "J", "suit" => "hearts"],
        ["value" => "J", "suit" => "diamonds"],
        ["value" => "J", "suit" => "clubs"],
        ["value" => "K", "suit" => "spades"],
        ["value" => "2", "suit" => "clubs"],
    ];
    $playerHole = [
        ["value" => "A", "suit" => "hearts"],
        ["value" => "3", "suit" => "diamonds"],
    ];
    expect(findRule($community, $playerHole))->toBe(6);
});

// 7 - Two pair: Tens and Nines, no three of a kind
test('rules returns 7 for two pair', function () {
    $community = [
        ["value" => "10", "suit" => "hearts"],
        ["value" => "10", "suit" => "diamonds"],
        ["value" => "9",  "suit" => "clubs"],
        ["value" => "K",  "suit" => "spades"],
        ["value" => "2",  "suit" => "clubs"],
    ];
    $playerHole = [
        ["value" => "9",  "suit" => "hearts"],
        ["value" => "3",  "suit" => "diamonds"],
    ];
    expect(findRule($community, $playerHole))->toBe(7);
});

// 8 - One pair: pair of Queens, no two pair or better
test('rules returns 8 for one pair', function () {
    $community = [
        ["value" => "Q", "suit" => "hearts"],
        ["value" => "8", "suit" => "diamonds"],
        ["value" => "5", "suit" => "clubs"],
        ["value" => "3", "suit" => "spades"],
        ["value" => "2", "suit" => "clubs"],
    ];
    $playerHole = [
        ["value" => "Q", "suit" => "spades"],
        ["value" => "7", "suit" => "hearts"],
    ];
    expect(findRule($community, $playerHole))->toBe(8);
});

// 9 - High card: all 7 cards different values, no pair, no flush, no straight
test('rules returns 9 for high card', function () {
    $community = [
        ["value" => "A", "suit" => "hearts"],
        ["value" => "J", "suit" => "diamonds"],
        ["value" => "8", "suit" => "clubs"],
        ["value" => "5", "suit" => "spades"],
        ["value" => "3", "suit" => "hearts"],
    ];
    $playerHole = [
        ["value" => "K", "suit" => "clubs"],
        ["value" => "2", "suit" => "diamonds"],
    ];
    expect(findRule($community, $playerHole))->toBe(9);
});

// getWinner: single winner (player 0 has straight flush, rank 1)
test('getWinner returns [0] when first player has the best hand', function () {
    $playerRules = [1, 8, 3, 5, 3];
    expect(getWinner($playerRules))->toBe([0]);
});

// getWinner: single winner in the middle of the array
test('getWinner returns [1] when second player has the best hand', function () {
    $playerRules = [5, 2, 6, 8];
    expect(getWinner($playerRules))->toBe([1]);
});

// getWinner: two-way tie (players 1 and 3 both have rank 3)
test('getWinner returns [1, 3] when two players tie', function () {
    $playerRules = [8, 3, 5, 3];
    expect(getWinner($playerRules))->toBe([1, 3]);
});

// getWinner: all players tie
test('getWinner returns all indices when every player ties', function () {
    $playerRules = [9, 9, 9];
    expect(getWinner($playerRules))->toBe([0, 1, 2]);
});

// getWinner: single player always wins
test('getWinner returns [0] for a single player', function () {
    $playerRules = [4];
    expect(getWinner($playerRules))->toBe([0]);
});

// --- Tie-break: cases where getWinner incorrectly declares a tie ---
// Both players have rank 1 (straight flush) but player 0 has a royal flush (A-high)
// and player 1 has a 9-high straight flush. Player 0 should win, not a tie.
test('getWinner incorrectly declares a tie between royal flush and 9-high straight flush', function () {
    $community = [
        ["value" => "Q",  "suit" => "hearts"],
        ["value" => "J",  "suit" => "hearts"],
        ["value" => "10", "suit" => "hearts"],
        ["value" => "2",  "suit" => "clubs"],
        ["value" => "3",  "suit" => "diamonds"],
    ];
    $player0Hole = [
        ["value" => "A", "suit" => "hearts"],   // completes A-K-Q-J-10 royal flush
        ["value" => "K", "suit" => "hearts"],
    ];
    $player1Hole = [
        ["value" => "9", "suit" => "hearts"],   // completes 9-10-J-Q-K... wait, no K here
        ["value" => "8", "suit" => "hearts"],   // completes 8-9-10-J-Q straight flush
    ];
    $playerRules = [findRule($community, $player0Hole), findRule($community, $player1Hole)];
    // Both return rank 1 — getWinner sees a tie and returns [0, 1]
    expect($playerRules)->toBe([1, 1]);
    // But the correct winner is only player 0; this assertion fails, proving the gap
    expect(getWinner($playerRules))->toBe([0]);
});

// Both players have rank 8 (one pair) but player 1 has a higher pair (Ks vs Qs).
// Player 1 should win, not a tie.
test('getWinner incorrectly declares a tie between a pair of Kings and a pair of Queens', function () {
    $community = [
        ["value" => "5", "suit" => "hearts"],
        ["value" => "7", "suit" => "diamonds"],
        ["value" => "2", "suit" => "clubs"],
        ["value" => "3", "suit" => "spades"],
        ["value" => "9", "suit" => "hearts"],
    ];
    $player0Hole = [
        ["value" => "Q", "suit" => "hearts"],
        ["value" => "Q", "suit" => "spades"],   // pair of Queens
    ];
    $player1Hole = [
        ["value" => "K", "suit" => "hearts"],
        ["value" => "K", "suit" => "spades"],   // pair of Kings
    ];
    $playerRules = [findRule($community, $player0Hole), findRule($community, $player1Hole)];
    // Both return rank 8 — getWinner sees a tie and returns [0, 1]
    expect($playerRules)->toBe([8, 8]);
    // But the correct winner is only player 1; this assertion fails, proving the gap
    expect(getWinner($playerRules))->toBe([1]);
});

// Both players have rank 9 (high card) but player 0's best card is an Ace vs a King.
// Player 0 should win, not a tie.
test('getWinner incorrectly declares a tie between Ace-high and King-high', function () {
    $community = [
        ["value" => "8", "suit" => "hearts"],
        ["value" => "5", "suit" => "diamonds"],
        ["value" => "3", "suit" => "clubs"],
        ["value" => "J", "suit" => "spades"],
        ["value" => "2", "suit" => "hearts"],
    ];
    $player0Hole = [
        ["value" => "A", "suit" => "clubs"],    // Ace high
        ["value" => "4", "suit" => "diamonds"],
    ];
    $player1Hole = [
        ["value" => "K", "suit" => "clubs"],    // King high
        ["value" => "6", "suit" => "diamonds"],
    ];
    $playerRules = [findRule($community, $player0Hole), findRule($community, $player1Hole)];
    // Both return rank 9 — getWinner sees a tie and returns [0, 1]
    expect($playerRules)->toBe([9, 9]);
    // But the correct winner is only player 0; this assertion fails, proving the gap
    expect(getWinner($playerRules))->toBe([0]);
});

