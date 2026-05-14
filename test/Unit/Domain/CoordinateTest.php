<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Domain;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Tests\SnekSpec\Parser as SnekSpecParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CoordinateTest extends TestCase
{
    private function parse(string $ascii): GameState
    {
        return GameStateParserFactory::make()->parse((new SnekSpecParser())->parse($ascii));
    }

    #[Test]
    public function adjacent_offsets_by_one_in_each_direction(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            -----
            --S--
            --t--
            -----
            BOARD);
        $head = $state->you->head;

        self::assertSame($head->x, $head->adjacent(Direction::UP)->x);
        self::assertSame($head->y + 1, $head->adjacent(Direction::UP)->y);

        self::assertSame($head->x, $head->adjacent(Direction::DOWN)->x);
        self::assertSame($head->y - 1, $head->adjacent(Direction::DOWN)->y);

        self::assertSame($head->x - 1, $head->adjacent(Direction::LEFT)->x);
        self::assertSame($head->y, $head->adjacent(Direction::LEFT)->y);

        self::assertSame($head->x + 1, $head->adjacent(Direction::RIGHT)->x);
        self::assertSame($head->y, $head->adjacent(Direction::RIGHT)->y);
    }

    #[Test]
    public function adjacent_round_trips_to_origin_via_equals(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            -----
            --S--
            --t--
            -----
            BOARD);
        $head = $state->you->head;

        $there_and_back = $head->adjacent(Direction::UP)->adjacent(Direction::DOWN);
        self::assertTrue($head->equals($there_and_back));
    }

    #[Test]
    public function equals_compares_x_and_y(): void
    {
        $state = $this->parse(<<<BOARD
            -----
            --S--
            --t--
            -----
            BOARD);
        $head = $state->you->head;

        self::assertFalse($head->equals($head->adjacent(Direction::UP)));
        self::assertFalse($head->equals($head->adjacent(Direction::LEFT)));
    }
}