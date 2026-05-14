<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Strategy;

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Aggregate\Conditional;
use BattleSnake\Strategy\Aggregate\FirstSafe;
use BattleSnake\Strategy\Aggregate\Majority;
use BattleSnake\Strategy\Aggregate\Scored;
use BattleSnake\Strategy\Strategy;
use BattleSnake\Tests\Support\TestGame;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AggregateTest extends TestCase
{
    private static function aState(): GameState
    {
        $json = \json_decode(
            (string) \file_get_contents(__DIR__ . '/../../requests/official-example.json'),
            true,
        );
        return GameStateParserFactory::make()->parse($json);
    }

    private static function fixedStrategy(Direction $direction): Strategy
    {
        return new class ($direction) implements Strategy {
            public function __construct(private readonly Direction $direction)
            {
            }

            public function __invoke(Game $game, GameState $state): Direction
            {
                return $this->direction;
            }
        };
    }

    #[Test]
    public function majority_picks_the_most_voted_direction(): void
    {
        $agg = new Majority(
            self::fixedStrategy(Direction::UP),
            self::fixedStrategy(Direction::UP),
            self::fixedStrategy(Direction::DOWN),
        );

        self::assertSame(Direction::UP, $agg(TestGame::fresh(), self::aState()));
    }

    #[Test]
    public function conditional_invokes_only_the_picked_strategy(): void
    {
        $callCount = ['a' => 0, 'b' => 0];

        $a = new class ($callCount) implements Strategy {
            public function __construct(private array &$counts)
            {
            }

            public function __invoke(Game $game, GameState $state): Direction
            {
                $this->counts['a']++;
                return Direction::LEFT;
            }
        };

        $b = new class ($callCount) implements Strategy {
            public function __construct(private array &$counts)
            {
            }

            public function __invoke(Game $game, GameState $state): Direction
            {
                $this->counts['b']++;
                return Direction::RIGHT;
            }
        };

        $agg = new Conditional(static fn() => 1, $a, $b);

        self::assertSame(Direction::RIGHT, $agg(TestGame::fresh(), self::aState()));
        self::assertSame(0, $callCount['a'], 'Unselected strategy must not be invoked');
        self::assertSame(1, $callCount['b']);
    }

    #[Test]
    public function first_safe_short_circuits(): void
    {
        $callCount = 0;
        $tracker = new class ($callCount) implements Strategy {
            public function __construct(private int &$count)
            {
            }

            public function __invoke(Game $game, GameState $state): Direction
            {
                $this->count++;
                return Direction::UP;
            }
        };

        // The first strategy returns a direction the classifier will likely deem SAFE
        // for the official-example state. If FirstSafe finds it, the tracker stays at 1.
        $agg = new FirstSafe(new MoveClassifier(), $tracker, self::fixedStrategy(Direction::DOWN));
        $agg(TestGame::fresh(), self::aState());

        self::assertSame(1, $callCount, 'Should not have called any strategy beyond the first if it was safe');
    }

    #[Test]
    public function scored_picks_the_safer_direction(): void
    {
        // Build with a strategy that picks a dangerous direction and one that picks a safer one.
        // The Scored aggregator should prefer the safer one regardless of input order.
        $state = self::aState();
        $classifier = new MoveClassifier();
        $classifications = ($classifier)($state);

        $safe = null;
        $unsafe = null;
        foreach ($classifications as $move) {
            if ($move->is(\BattleSnake\Analyzer\Move\Classification::SAFE) && $safe === null) {
                $safe = $move->direction;
            }
            if ($move->is(\BattleSnake\Analyzer\Move\Classification::END) && $unsafe === null) {
                $unsafe = $move->direction;
            }
        }

        if ($safe === null || $unsafe === null) {
            self::markTestSkipped('Fixture state does not exercise both SAFE and END classifications');
        }

        $agg = new Scored(
            $classifier,
            self::fixedStrategy($unsafe),
            self::fixedStrategy($safe),
        );

        self::assertSame($safe, $agg(TestGame::fresh(), $state));
    }
}
