<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Support;

use BattleSnake\EventSourcing\Game;
use BattleSnake\EventSourcing\GameId;
use BattleSnake\EventSourcing\SerdePayloadSerializer;
use Closure;
use Crell\Serde\SerdeCommon;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\TestUtilities\AggregateRootTestCase as EventSauceAggregateRootTestCase;

/**
 * Base test case for given/when/then scenarios on the Game aggregate.
 *
 * Pattern (see https://eventsauce.io/docs/testing/):
 *
 *     final class SomeGameScenarioTest extends AggregateRootTestCase
 *     {
 *         #[Test]
 *         public function some_command_records_some_event(): void
 *         {
 *             $this->given(new PriorEvent())
 *                 ->when(fn(Game $game) => $game->doSomething())
 *                 ->then(new SomeEvent());
 *         }
 *     }
 *
 * This class wires every Game-specific concern once:
 *   - the aggregate root type (Game) and a fresh GameId per test
 *   - the Serde-backed payload serializer, so events carrying GameState
 *     value objects round-trip through the in-memory message store
 *   - a generic handle() so when() can take a Closure that drives the aggregate
 *     and we persist on the way out — no need for each test class to repeat it
 */
abstract class AggregateRootTestCase extends EventSauceAggregateRootTestCase
{
    protected function newAggregateRootId(): AggregateRootId
    {
        return GameId::generate();
    }

    protected function aggregateRootClassName(): string
    {
        return Game::class;
    }

    protected function messageSerializer(): MessageSerializer
    {
        return new ConstructingMessageSerializer(
            $this->classNameInflector(),
            new SerdePayloadSerializer(new SerdeCommon()),
        );
    }

    /**
     * Invoked by AggregateRootTestCase::when(). The closure receives the
     * rehydrated aggregate; any commands it records will be persisted (and
     * surfaced to then()/expectToFail()) when the closure returns normally.
     *
     * @param Closure(Game): void $action
     */
    protected function handle(Closure $action): void
    {
        $aggregate = $this->repository->retrieve($this->aggregateRootId());
        $action($aggregate);
        $this->repository->persist($aggregate);
    }
}