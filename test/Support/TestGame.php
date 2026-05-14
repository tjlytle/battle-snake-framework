<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Support;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\EventSourcing\Events\Move;
use BattleSnake\EventSourcing\Events\Nudge;
use BattleSnake\EventSourcing\Events\Start;
use BattleSnake\EventSourcing\Events\Turn;
use BattleSnake\EventSourcing\Game;
use BattleSnake\EventSourcing\GameId;
use Generator;

/**
 * Strategy-test helper. Returns Game aggregates pre-populated with the
 * events you want — no repository, no database, no DI container.
 *
 *   $game = TestGame::fresh();                       // empty
 *   $game = TestGame::started();                     // Start applied
 *   $game = TestGame::withNudge(Direction::UP);      // Start + Nudge applied
 *   $game = TestGame::afterMoves(Direction::UP, Direction::RIGHT);
 */
final class TestGame
{
    public static function fresh(): Game
    {
        return Game::reconstituteFromEvents(GameId::generate(), self::generator());
    }

    public static function started(?GameState $initial = null): Game
    {
        return Game::reconstituteFromEvents(
            GameId::generate(),
            self::generator(new Start($initial ?? self::stubState())),
        );
    }

    public static function withNudge(Direction $direction, ?GameState $initial = null): Game
    {
        return Game::reconstituteFromEvents(
            GameId::generate(),
            self::generator(
                new Start($initial ?? self::stubState()),
                new Nudge($direction),
            ),
        );
    }

    public static function afterMoves(Direction ...$moves): Game
    {
        $events = [new Start(self::stubState())];
        foreach ($moves as $direction) {
            $events[] = new Turn(self::stubState());
            $events[] = new Move($direction);
        }

        return Game::reconstituteFromEvents(
            GameId::generate(),
            self::generator(...$events),
        );
    }

    /**
     * @return Generator<int, object, mixed, int>
     */
    private static function generator(object ...$events): Generator
    {
        foreach ($events as $event) {
            yield $event;
        }
        return \count($events);
    }

    private static function stubState(): GameState
    {
        /** @var GameState|null $cached */
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $json = \json_decode(
            (string) \file_get_contents(__DIR__ . '/../requests/official-example.json'),
            true,
        );
        return $cached = GameStateParserFactory::make()->parse($json);
    }
}
