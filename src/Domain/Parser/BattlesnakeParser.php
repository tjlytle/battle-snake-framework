<?php

declare(strict_types=1);

namespace BattleSnake\Domain\Parser;

use BattleSnake\Domain\Battlesnake;
use BattleSnake\Domain\Customizations;

final readonly class BattlesnakeParser
{
    public function __construct(
        private CoordinateFactory $coordinate_factory,
    ) {
    }

    public function parse(array $data): Battlesnake
    {
        $head = null;
        if (isset($data['head']) && \is_array($data['head'])) {
            $head = $this->coordinate_factory->createFromArray($data['head']);
        }

        $customizations = $data['customizations'] ?? [];

        return new Battlesnake(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            health: (int)($data['health'] ?? 0),
            body: $this->coordinate_factory->createCollectionFromArray($data['body'] ?? []),
            latency: $data['latency'] ?? '',
            head: $head,
            length: (int)($data['length'] ?? 0),
            shout: $data['shout'] ?? '',
            squad: $data['squad'] ?? '',
            customizations: new Customizations(
                $customizations['color'] ?? null,
                $customizations['head'] ?? null,
                $customizations['tail'] ?? null,
            ),
        );
    }
}
