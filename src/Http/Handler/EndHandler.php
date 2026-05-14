<?php

declare(strict_types=1);

namespace BattleSnake\Http\Handler;

use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\EventSourcing\GameId;
use BattleSnake\EventSourcing\InvalidState;
use EventSauce\EventSourcing\AggregateRootRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EndHandler extends AbstractHandler
{
    public function __construct(
        ResponseFactoryInterface $response_factory,
        private readonly AggregateRootRepository $games,
    ) {
        parent::__construct($response_factory);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $state = GameStateParserFactory::make()->parse($request->getParsedBody());
        $game_id = GameId::fromString($state->game->id);

        $game = $this->games->retrieve($game_id);

        try {
            $game->end($state);
        } catch (InvalidState) {
            return $this->response_factory->createResponse(409);
        }

        $this->games->persist($game);
        return $this->response_factory->createResponse(204);
    }
}
