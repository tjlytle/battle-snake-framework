<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Domain;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Tests\SnekSpec\Parser as SnekSpecParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BattlesnakeTest extends TestCase
{
    private function parse(string $ascii): GameState
    {
        return GameStateParserFactory::make()->parse(new SnekSpecParser()->parse($ascii));
    }

    #[Test]
    public function occupies_returns_true_for_every_body_segment(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            --T--
            --t--
            --S--
            -----
            BOARD);
        $you = $state->you;

        self::assertCount(3, \iterator_to_array($you->body));
        foreach ($you->body as $segment) {
            self::assertTrue(
                $you->occupies($segment),
                "Snake should report itself as occupying ({$segment->x},{$segment->y})",
            );
        }
    }

    #[Test]
    public function occupies_returns_false_for_an_empty_cell(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            --T--
            --t--
            --S--
            -----
            BOARD);
        $you = $state->you;

        // (0,0) is empty in this board.
        $empty = $you->head->adjacent(Direction::LEFT)->adjacent(Direction::LEFT);
        self::assertFalse($you->occupies($empty));
    }

    #[Test]
    public function could_reach_includes_all_four_head_neighbors(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            --S--
            --t--
            -----
            BOARD);
        $you = $state->you;
        $head = $you->head;

        foreach (Direction::cases() as $direction) {
            self::assertTrue(
                $you->couldReach($head->adjacent($direction)),
                "Snake should be able to reach the cell {$direction->value} of its head",
            );
        }
    }

    #[Test]
    public function could_reach_excludes_cells_more_than_one_step_away(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            -----
            --S--
            --t--
            -----
            BOARD);
        $head = $state->you->head;
        $two_up = $head->adjacent(Direction::UP)->adjacent(Direction::UP);

        self::assertFalse($state->you->couldReach($two_up));
        self::assertFalse($state->you->couldReach($head));
    }
}