<?php

declare(strict_types=1);

namespace BattleSnake\Http\Handler;

use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\EventSourcing\GameId;
use BattleSnake\EventSourcing\InvalidState;
use BattleSnake\Strategy\Strategy;
use EventSauce\EventSourcing\AggregateRootRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MoveHandler extends AbstractHandler
{
    public function __construct(
        ResponseFactoryInterface $response_factory,
        private readonly AggregateRootRepository $games,
        private readonly Strategy $strategy,
    ) {
        parent::__construct($response_factory);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $state = GameStateParserFactory::make()->parse($request->getParsedBody());
        $game_id = GameId::fromString($state->game->id);

        $game = $this->games->retrieve($game_id);

        try {
            $direction = $game->turn($state, $this->strategy);
        } catch (InvalidState) {
            return $this->response_factory->createResponse(409);
        }

        $this->games->persist($game);

        return $this->createJsonResponse([
            'move' => $direction->value,
            'shout' => 'Moving ' . $direction->value . '!',
        ]);
    }
}
