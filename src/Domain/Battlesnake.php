<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

final readonly class Battlesnake
{
    public function __construct(
        public string $id,
        public string $name,
        public int $health,
        public CoordinateCollection $body = new CoordinateCollection(),
        public string $latency = '',
        public Coordinate|null $head = null,
        public int $length = 0,
        public string $shout = '',
        public string $squad = '',
        public Customizations $customizations = new Customizations(),
    ) {
    }

    /** True if any of this snake's body segments sit on $position. */
    public function occupies(Coordinate $position): bool
    {
        foreach ($this->body as $segment) {
            if ($segment->equals($position)) {
                return true;
            }
        }
        return false;
    }

    /** True if this snake's head could move to $position next turn. */
    public function couldReach(Coordinate $position): bool
    {
        if ($this->head === null) {
            return false;
        }
        foreach (Direction::cases() as $direction) {
            if ($this->head->adjacent($direction)->equals($position)) {
                return true;
            }
        }
        return false;
    }
}
