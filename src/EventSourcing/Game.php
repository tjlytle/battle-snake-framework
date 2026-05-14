<?php

declare(strict_types=1);

namespace BattleSnake\EventSourcing;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Events\End;
use BattleSnake\EventSourcing\Events\Move;
use BattleSnake\EventSourcing\Events\Nudge;
use BattleSnake\EventSourcing\Events\Start;
use BattleSnake\EventSourcing\Events\Turn;
use BattleSnake\Strategy\Strategy;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;

/**
 * The lifecycle of one Battlesnake game.
 *
 * Historical state is exposed via public readonly-from-outside properties so
 * Strategies can read it ($game->last_nudge, $game->turn, etc.) without
 * needing to know anything about event sourcing. Mutation is confined to
 * the protected apply* methods.
 */
final class Game implements AggregateRoot
{
    use AggregateRootBehaviour;

    public private(set) bool $is_started = false;
    public private(set) bool $is_finished = false;
    public private(set) int $turn = 0;
    public private(set) ?Direction $last_nudge = null;
    public private(set) ?Direction $last_move = null;

    public static function create(AggregateRootId $id): self
    {
        return self::createNewInstance($id);
    }

    public function start(GameState $initial): void
    {
        if ($this->is_started) {
            throw new InvalidState('Game already started');
        }
        $this->recordThat(new Start($initial));
    }

    /**
     * Perceive the new game state, decide a move via the given Strategy,
     * emit Turn(state) followed by Move(direction), and return the move.
     */
    public function turn(GameState $state, Strategy $strategy): Direction
    {
        if (! $this->is_started || $this->is_finished) {
            throw new InvalidState('Game is not in a playable state');
        }

        $this->recordThat(new Turn($state));

        $direction = ($strategy)($this, $state);

        $this->recordThat(new Move($direction));
        return $direction;
    }

    public function nudge(Direction $direction): void
    {
        if (! $this->is_started || $this->is_finished) {
            throw new InvalidState('Cannot nudge an inactive game');
        }
        $this->recordThat(new Nudge($direction));
    }

    public function end(GameState $final): void
    {
        if (! $this->is_started || $this->is_finished) {
            throw new InvalidState('Game cannot be ended');
        }
        $this->recordThat(new End($final));
    }

    protected function applyStart(Start $_event): void
    {
        $this->is_started = true;
    }

    protected function applyTurn(Turn $_event): void
    {
        $this->turn++;
    }

    protected function applyMove(Move $event): void
    {
        $this->last_move = $event->direction;
        $this->last_nudge = null;
    }

    protected function applyNudge(Nudge $event): void
    {
        $this->last_nudge = $event->direction;
    }

    protected function applyEnd(End $_event): void
    {
        $this->is_finished = true;
    }
}
