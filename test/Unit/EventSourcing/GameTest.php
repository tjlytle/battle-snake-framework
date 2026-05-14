<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\EventSourcing;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\EventSourcing\Events\End;
use BattleSnake\EventSourcing\Events\Move;
use BattleSnake\EventSourcing\Events\Nudge;
use BattleSnake\EventSourcing\Events\Start;
use BattleSnake\EventSourcing\Events\Turn;
use BattleSnake\EventSourcing\Game;
use BattleSnake\EventSourcing\InvalidState;
use BattleSnake\Strategy\Strategy;
use BattleSnake\Tests\SnekSpec\Parser as SnekSpecParser;
use BattleSnake\Tests\Support\AggregateRootTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Given/When/Then scenarios for the Game aggregate.
 *
 *   - given(...past events that hydrate state)
 *   - when(closure that drives a command)
 *   - then(...expected events from this commit)
 *
 * AggregateRootTestCase asserts in tearDown — no explicit asserts needed.
 */
final class GameTest extends AggregateRootTestCase
{
    private static function aState(): GameState
    {
        $ascii = <<<BOARD
            -----
            --S--
            --t--
            -----
            BOARD;
        return GameStateParserFactory::make()->parse((new SnekSpecParser())->parse($ascii));
    }

    private static function fixed(Direction $direction): Strategy
    {
        return new class ($direction) implements Strategy {
            public function __construct(private readonly Direction $direction)
            {
            }

            public function __invoke($game, $state): Direction
            {
                return $this->direction;
            }
        };
    }

    #[Test]
    public function starting_a_fresh_game_records_a_start_event(): void
    {
        $state = self::aState();

        $this->when(static fn(Game $game) => $game->start($state))
            ->then(new Start($state));
    }

    #[Test]
    public function starting_an_already_started_game_fails(): void
    {
        $state = self::aState();

        $this->given(new Start($state))
            ->when(static fn(Game $game) => $game->start($state))
            ->expectToFail(new InvalidState('Game already started'));
    }

    #[Test]
    public function a_turn_records_turn_then_move(): void
    {
        $state = self::aState();

        $this->given(new Start($state))
            ->when(static fn(Game $game) => $game->turn($state, self::fixed(Direction::RIGHT)))
            ->then(new Turn($state), new Move(Direction::RIGHT));
    }

    #[Test]
    public function a_nudge_records_a_nudge_event(): void
    {
        $state = self::aState();

        $this->given(new Start($state))
            ->when(static fn(Game $game) => $game->nudge(Direction::LEFT))
            ->then(new Nudge(Direction::LEFT));
    }

    #[Test]
    public function nudging_an_unstarted_game_fails(): void
    {
        $this->when(static fn(Game $game) => $game->nudge(Direction::UP))
            ->expectToFail(new InvalidState('Cannot nudge an inactive game'));
    }

    #[Test]
    public function ending_a_started_game_records_end(): void
    {
        $state = self::aState();

        $this->given(new Start($state))
            ->when(static fn(Game $game) => $game->end($state))
            ->then(new End($state));
    }

    #[Test]
    public function taking_a_turn_after_end_fails(): void
    {
        $state = self::aState();

        $this->given(new Start($state), new End($state))
            ->when(static fn(Game $game) => $game->turn($state, self::fixed(Direction::UP)))
            ->expectToFail(new InvalidState('Game is not in a playable state'));
    }

    #[Test]
    public function move_consumes_a_pending_nudge_in_aggregate_state(): void
    {
        // Sanity check that apply* methods correctly mutate the public state.
        // Most other behavior is covered by event assertions above.
        $state = self::aState();
        $game = Game::reconstituteFromEvents(
            $this->aggregateRootId(),
            (function () use ($state) {
                yield new Start($state);
                yield new Nudge(Direction::LEFT);
                yield new Turn($state);
                yield new Move(Direction::UP);
                return 4;
            })(),
        );

        self::assertTrue($game->is_started);
        self::assertSame(Direction::UP, $game->last_move);
        self::assertNull($game->last_nudge, 'Move should have cleared the pending nudge');
        self::assertSame(1, $game->turn);
    }
}