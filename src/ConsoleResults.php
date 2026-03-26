<?php

require_once __DIR__ . '/ResultsInterface.php';

class ConsoleResults implements ResultsInterface
{
    public function showCards(array $community, array $holes): void
    {
        echo "Generated cards:\n";
        echo "Community:\n";
        foreach ($community as $card) {
            echo "  {$card['value']} of {$card['suit']}\n";
        }
        foreach ($holes as $i => $hole) {
            $player = $i + 1;
            echo "Player {$player} hole cards:\n";
            foreach ($hole as $card) {
                echo "  {$card['value']} of {$card['suit']}\n";
            }
        }
    }

    public function showPlayerRule(int $player, int $rule): void
    {
        $names = [
            1 => 'Straight flush',
            2 => 'Four of a kind',
            3 => 'Full house',
            4 => 'Flush',
            5 => 'Straight',
            6 => 'Three of a kind',
            7 => 'Two pair',
            8 => 'One pair',
            9 => 'High card',
        ];
        $label = $names[$rule] ?? "Unknown ({$rule})";
        echo "\nRule associated with player " . ($player + 1) . " is {$rule} ({$label})";
    }

    public function showWinner(array $winnerIndices): void
    {
        if (count($winnerIndices) === 1) {
            echo "\nThe winner is player " . ($winnerIndices[0] + 1) . " !\n";
        } else {
            $players = implode(', ', array_map(fn($i) => $i + 1, $winnerIndices));
            echo "\nIt's a tie between players: {$players} !\n";
        }
    }
}
