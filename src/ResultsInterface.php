<?php

interface ResultsInterface
{
    public function showCards(array $community, array $holes): void;
    public function showPlayerRule(int $player, int $rule): void;
    public function showWinner(array $winnerIndices): void;
}
