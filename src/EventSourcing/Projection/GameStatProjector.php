<?php

declare(strict_types=1);

namespace BattleSnake\EventSourcing\Projection;

use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Events\End;
use Doctrine\ORM\EntityManagerInterface;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageConsumer;

/**
 * Projects End events into the `game_stats` Doctrine read model.
 *
 * Reads everything it needs from the End event's GameState — does not retrieve
 * the aggregate from the repository, which would create a DI cycle.
 */
final class GameStatProjector implements MessageConsumer
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function handle(Message $message): void
    {
        $event = $message->event();
        if (! $event instanceof End) {
            return;
        }

        $header = $message->header(Header::AGGREGATE_ROOT_ID);
        $aggregate_id = $header instanceof AggregateRootId ? $header->toString() : (string) $header;

        $stat = new GameStat(
            aggregate_id: $aggregate_id,
            win: $this->determineWin($event->state),
            length: $event->state->you->length,
            survived: $event->state->turn,
        );

        $this->em->persist($stat);
        $this->em->flush();
    }

    private function determineWin(GameState $final): bool
    {
        $you = $final->you;
        foreach ($final->board->snakes as $snake) {
            if ($snake->id === $you->id) {
                return true;
            }
        }
        return false;
    }
}
