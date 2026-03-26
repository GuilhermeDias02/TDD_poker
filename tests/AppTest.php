<?php

require_once __DIR__ . '/../src/Game.php';

// Shared Game instance for all tests
$game = new Game();

test('generateCards returns 5 community cards and 2 hole cards for 2 players', function () use ($game) {
    $nbPlayers = 2;
    $cards = $game->generateCards($nbPlayers);
    expect(count($cards["community"]))->toBe(5);
    for ($i = 0; $i < $nbPlayers; $i++) {
        expect(count($cards["holes"][$i]))->toBe(2);
    }
});

// 1 - Straight flush: A-K-Q-J-10 all hearts
test('rules returns 1 for straight flush', function () use ($game) {
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
    expect($game->findRule($community, $playerHole))->toBe(1);
});

// 2 - Four of a kind: four Aces
test('rules returns 2 for four of a kind', function () use ($game) {
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
    expect($game->findRule($community, $playerHole))->toBe(2);
});

// 3 - Full house: three Kings + two Queens
test('rules returns 3 for full house', function () use ($game) {
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
    expect($game->findRule($community, $playerHole))->toBe(3);
});

// 4 - Flush: five spades, non-sequential (not a straight flush)
test('rules returns 4 for flush', function () use ($game) {
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
    expect($game->findRule($community, $playerHole))->toBe(4);
});

// 5 - Straight: 5-6-7-8-9 mixed suits (not a flush)
test('rules returns 5 for straight', function () use ($game) {
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
    expect($game->findRule($community, $playerHole))->toBe(5);
});

// 6 - Three of a kind: three Jacks, no pair among remaining cards
test('rules returns 6 for three of a kind', function () use ($game) {
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
    expect($game->findRule($community, $playerHole))->toBe(6);
});

// 7 - Two pair: Tens and Nines, no three of a kind
test('rules returns 7 for two pair', function () use ($game) {
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
    expect($game->findRule($community, $playerHole))->toBe(7);
});

// 8 - One pair: pair of Queens, no two pair or better
test('rules returns 8 for one pair', function () use ($game) {
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
    expect($game->findRule($community, $playerHole))->toBe(8);
});

// 9 - High card: all 7 cards different values, no pair, no flush, no straight
test('rules returns 9 for high card', function () use ($game) {
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
    expect($game->findRule($community, $playerHole))->toBe(9);
});

// getWinner: single winner (player 0 has straight flush, best hand)
test('getWinner returns [0] when first player has the best hand', function () use ($game) {
    $community = [
        ["value" => "A", "suit" => "hearts"],
        ["value" => "K", "suit" => "hearts"],
        ["value" => "Q", "suit" => "hearts"],
        ["value" => "J", "suit" => "hearts"],
        ["value" => "2", "suit" => "clubs"],
    ];
    $holes = [
        [["value" => "10", "suit" => "hearts"], ["value" => "3", "suit" => "diamonds"]], // straight flush
        [["value" => "8",  "suit" => "clubs"],  ["value" => "7", "suit" => "spades"]],   // high card
        [["value" => "Q",  "suit" => "spades"], ["value" => "Q", "suit" => "clubs"]],    // full house
        [["value" => "5",  "suit" => "diamonds"],["value" => "6","suit" => "clubs"]],    // straight
        [["value" => "K",  "suit" => "clubs"],  ["value" => "K", "suit" => "spades"]],   // full house
    ];
    expect($game->getWinner($community, $holes))->toBe([0]);
});

// getWinner: single winner in the middle of the array
test('getWinner returns [1] when second player has the best hand', function () use ($game) {
    $community = [
        ["value" => "A", "suit" => "hearts"],
        ["value" => "A", "suit" => "diamonds"],
        ["value" => "A", "suit" => "clubs"],
        ["value" => "2", "suit" => "spades"],
        ["value" => "3", "suit" => "clubs"],
    ];
    $holes = [
        [["value" => "5", "suit" => "hearts"],  ["value" => "6", "suit" => "clubs"]],   // three of a kind
        [["value" => "A", "suit" => "spades"],  ["value" => "K", "suit" => "hearts"]],  // four of a kind
        [["value" => "7", "suit" => "diamonds"],["value" => "8", "suit" => "clubs"]],   // three of a kind
        [["value" => "9", "suit" => "spades"],  ["value" => "4", "suit" => "hearts"]],  // three of a kind
    ];
    expect($game->getWinner($community, $holes))->toBe([1]);
});

// getWinner: two-way true tie (identical hands, same ranks, same kickers)
test('getWinner returns [1, 3] when two players genuinely tie', function () use ($game) {
    $community = [
        ["value" => "A", "suit" => "hearts"],
        ["value" => "K", "suit" => "diamonds"],
        ["value" => "Q", "suit" => "clubs"],
        ["value" => "2", "suit" => "spades"],
        ["value" => "3", "suit" => "hearts"],
    ];
    $holes = [
        [["value" => "5",  "suit" => "clubs"],  ["value" => "6",  "suit" => "diamonds"]], // high card A, kicker K Q 6 5
        [["value" => "J",  "suit" => "clubs"],  ["value" => "J",  "suit" => "diamonds"]], // pair of Jacks
        [["value" => "10", "suit" => "hearts"], ["value" => "9",  "suit" => "spades"]],   // high card A, kicker K Q 10 9
        [["value" => "J",  "suit" => "hearts"], ["value" => "J",  "suit" => "spades"]],   // pair of Jacks (same as player 1)
    ];
    expect($game->getWinner($community, $holes))->toBe([1, 3]);
});

// getWinner: all players genuinely tie (identical community, low hole cards that don't affect top 5)
test('getWinner returns all indices when every player genuinely ties', function () use ($game) {
    $community = [
        ["value" => "A", "suit" => "hearts"],
        ["value" => "K", "suit" => "diamonds"],
        ["value" => "Q", "suit" => "clubs"],
        ["value" => "J", "suit" => "spades"],
        ["value" => "10","suit" => "hearts"],
    ];
    $holes = [
        [["value" => "2", "suit" => "clubs"],  ["value" => "3", "suit" => "diamonds"]],
        [["value" => "2", "suit" => "hearts"], ["value" => "3", "suit" => "spades"]],
        [["value" => "2", "suit" => "spades"], ["value" => "3", "suit" => "clubs"]],
    ];
    expect($game->getWinner($community, $holes))->toBe([0, 1, 2]);
});

// getWinner: single player always wins
test('getWinner returns [0] for a single player', function () use ($game) {
    $community = [
        ["value" => "A", "suit" => "hearts"],
        ["value" => "2", "suit" => "clubs"],
        ["value" => "5", "suit" => "diamonds"],
        ["value" => "8", "suit" => "spades"],
        ["value" => "J", "suit" => "clubs"],
    ];
    $holes = [
        [["value" => "K", "suit" => "clubs"], ["value" => "3", "suit" => "hearts"]],
    ];
    expect($game->getWinner($community, $holes))->toBe([0]);
});

// --- Tie-break: getWinner resolves these correctly via findScore ---
// Player 0 has a royal flush (A-high), player 1 has a 9-high straight flush.
test('getWinner returns [0] for royal flush over 9-high straight flush', function () use ($game) {
    $community = [
        ["value" => "Q",  "suit" => "hearts"],
        ["value" => "J",  "suit" => "hearts"],
        ["value" => "10", "suit" => "hearts"],
        ["value" => "2",  "suit" => "clubs"],
        ["value" => "3",  "suit" => "diamonds"],
    ];
    $holes = [
        [["value" => "A", "suit" => "hearts"], ["value" => "K", "suit" => "hearts"]], // royal flush
        [["value" => "9", "suit" => "hearts"], ["value" => "8", "suit" => "hearts"]], // 9-high straight flush
    ];
    expect($game->getWinner($community, $holes))->toBe([0]);
});

// Player 1 has a pair of Kings, player 0 has a pair of Queens.
test('getWinner returns [1] for pair of Kings over pair of Queens', function () use ($game) {
    $community = [
        ["value" => "5", "suit" => "hearts"],
        ["value" => "7", "suit" => "diamonds"],
        ["value" => "2", "suit" => "clubs"],
        ["value" => "3", "suit" => "spades"],
        ["value" => "9", "suit" => "hearts"],
    ];
    $holes = [
        [["value" => "Q", "suit" => "hearts"], ["value" => "Q", "suit" => "spades"]], // pair of Queens
        [["value" => "K", "suit" => "hearts"], ["value" => "K", "suit" => "spades"]], // pair of Kings
    ];
    expect($game->getWinner($community, $holes))->toBe([1]);
});

// Player 0 has Ace-high, player 1 has King-high.
test('getWinner returns [0] for Ace-high over King-high', function () use ($game) {
    $community = [
        ["value" => "8", "suit" => "hearts"],
        ["value" => "5", "suit" => "diamonds"],
        ["value" => "3", "suit" => "clubs"],
        ["value" => "J", "suit" => "spades"],
        ["value" => "2", "suit" => "hearts"],
    ];
    $holes = [
        [["value" => "A", "suit" => "clubs"], ["value" => "4", "suit" => "diamonds"]], // Ace high
        [["value" => "K", "suit" => "clubs"], ["value" => "6", "suit" => "diamonds"]], // King high
    ];
    expect($game->getWinner($community, $holes))->toBe([0]);
});
