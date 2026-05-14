<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Snake;

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Snake\RandomSnake;
use BattleSnake\Tests\Support\TestGame;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Demonstrates the participant-facing strategy testing pattern:
 *   1. Use the SnekSpec Parser (or a JSON fixture) to build a GameState.
 *   2. Use TestGame::* to build a Game aggregate with whatever history matters.
 *   3. Invoke the strategy directly and assert on the returned Direction.
 */
final class RandomSnakeTest extends TestCase
{
    private RandomSnake $snake;

    protected function setUp(): void
    {
        $this->snake = new RandomSnake(new MoveClassifier());
    }

    #[Test]
    public function picks_some_valid_direction_for_the_official_example_state(): void
    {
        $state = GameStateParserFactory::make()->parse(
            \json_decode((string) \file_get_contents(__DIR__ . '/../../requests/official-example.json'), true),
        );

        $direction = ($this->snake)(TestGame::fresh(), $state);

        self::assertContains(
            $direction,
            [Direction::UP, Direction::DOWN, Direction::LEFT, Direction::RIGHT],
            'RandomSnake must always return one of the four directions.',
        );
    }

    #[Test]
    public function strategies_can_read_last_move_from_game(): void
    {
        // The Game aggregate exposes last_move as a public readonly-from-outside property.
        // Participants can read it to bias their strategy (avoid U-turns, repeat momentum, etc.).
        $game = TestGame::afterMoves(Direction::UP);

        self::assertSame(Direction::UP, $game->last_move);
        // The strategy is welcome to use $game->last_move; we just confirm here it's accessible.
    }
}
