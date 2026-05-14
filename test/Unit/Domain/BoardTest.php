<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Domain;

use BattleSnake\Domain\Coordinate;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Tests\SnekSpec\Parser as SnekSpecParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BoardTest extends TestCase
{
    private function parse(string $ascii): GameState
    {
        return GameStateParserFactory::make()->parse((new SnekSpecParser())->parse($ascii));
    }

    #[Test]
    public function contains_accepts_inside_and_rejects_outside(): void
    {
        // 5x5 board.
        $board = $this->parse(<<<BOARD
            -----
            -----
            --S--
            --t--
            -----
            BOARD)->board;

        self::assertTrue($board->contains(new Coordinate(0, 0)));
        self::assertTrue($board->contains(new Coordinate($board->width - 1, $board->height - 1)));

        self::assertFalse($board->contains(new Coordinate(-1, 0)));
        self::assertFalse($board->contains(new Coordinate(0, -1)));
        self::assertFalse($board->contains(new Coordinate($board->width, 0)));
        self::assertFalse($board->contains(new Coordinate(0, $board->height)));
    }

    #[Test]
    public function has_food_at_finds_food_positions(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            --0--
            --S--
            --t--
            -----
            BOARD);
        $board = $state->board;
        $food = \iterator_to_array($board->food)[0];

        self::assertTrue($board->hasFoodAt($food));
        self::assertFalse($board->hasFoodAt(new Coordinate(0, 0)));
    }

    #[Test]
    public function has_hazard_at_finds_hazards(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            --/--
            --S--
            --t--
            -----
            BOARD);
        $board = $state->board;
        $hazard = \iterator_to_array($board->hazards)[0];

        self::assertTrue($board->hasHazardAt($hazard));
        self::assertFalse($board->hasHazardAt(new Coordinate(0, 0)));
    }

    #[Test]
    public function has_snake_at_finds_any_snake_segment(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            --T--
            --t--
            --S--
            -----
            BOARD);
        $board = $state->board;

        foreach ($state->you->body as $segment) {
            self::assertTrue($board->hasSnakeAt($segment));
        }
        // (0,0) is empty.
        self::assertFalse($board->hasSnakeAt(new Coordinate(0, 0)));
    }

    #[Test]
    public function is_threatened_for_detects_adjacent_enemy_head(): void
    {
        // S is our head at (2, 4); U is an opponent head at (2, 1).
        // The cell (2, 2) is adjacent to U, so it's threatened for us.
        $state = $this->parse(<<<BOARD
            -----
            --S--
            --t--
            -----
            --U--
            -----
            BOARD);
        $board = $state->board;

        // Find the opponent head — it's the only snake whose id != our id.
        $opponent = null;
        foreach ($board->snakes as $snake) {
            if ($snake->id !== $state->you->id) {
                $opponent = $snake;
                break;
            }
        }
        self::assertNotNull($opponent, 'Test fixture must have a second snake');

        foreach (Direction::cases() as $direction) {
            self::assertTrue(
                $board->isThreatenedFor($opponent->head->adjacent($direction), $state->you),
                "Cell {$direction->value} of opponent's head should be threatened",
            );
        }
    }

    #[Test]
    public function is_threatened_for_excludes_the_named_snake(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            --S--
            --t--
            -----
            --U--
            -----
            BOARD);
        $board = $state->board;
        $head = $state->you->head;

        // Cells adjacent to OUR head are not "threatened" — we don't threaten ourselves.
        // (None of them happen to be adjacent to U either in this fixture.)
        foreach (Direction::cases() as $direction) {
            self::assertFalse(
                $board->isThreatenedFor($head->adjacent($direction), $state->you),
                "Cell {$direction->value} of our own head should not be threatened by us",
            );
        }
    }
}