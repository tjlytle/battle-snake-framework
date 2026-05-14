<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

final readonly class Coordinate
{
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }

    public function adjacent(Direction $direction): self
    {
        return match ($direction) {
            Direction::UP    => new self($this->x, $this->y + 1),
            Direction::DOWN  => new self($this->x, $this->y - 1),
            Direction::LEFT  => new self($this->x - 1, $this->y),
            Direction::RIGHT => new self($this->x + 1, $this->y),
        };
    }

    public function equals(self $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }
}
