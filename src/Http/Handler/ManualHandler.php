<?php

declare(strict_types=1);

namespace BattleSnake\Http\Handler;

use BattleSnake\Domain\Direction;
use BattleSnake\EventSourcing\GameId;
use BattleSnake\EventSourcing\InvalidState;
use EventSauce\EventSourcing\AggregateRootRepository;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Teaching example of a custom endpoint. Accepts a {game_id, direction} body
 * and records a Nudge event on the named game.
 */
class ManualHandler extends AbstractHandler
{
    public function __construct(
        ResponseFactoryInterface $response_factory,
        private readonly AggregateRootRepository $games,
    ) {
        parent::__construct($response_factory);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (! \is_array($body) || ! isset($body['game_id'])) {
            return $this->response_factory->createResponse(400);
        }

        try {
            $game_id = GameId::fromString($body['game_id']);
        } catch (InvalidArgumentException) {
            return $this->response_factory->createResponse(404);
        }

        $direction = Direction::tryFrom($body['direction'] ?? '');
        if ($direction === null) {
            return $this->response_factory->createResponse(400);
        }

        $game = $this->games->retrieve($game_id);

        try {
            $game->nudge($direction);
        } catch (InvalidState) {
            return $this->response_factory->createResponse(400);
        }

        $this->games->persist($game);
        return $this->response_factory->createResponse(202);
    }
}
