<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

final readonly class Board
{
    public function __construct(
        public int $height,
        public int $width,
        public CoordinateCollection $food = new CoordinateCollection(),
        public CoordinateCollection $hazards = new CoordinateCollection(),
        public BattlesnakeCollection $snakes = new BattlesnakeCollection(),
    ) {
    }

    /** True if the position is inside the board's playable area. */
    public function contains(Coordinate $position): bool
    {
        return $position->x >= 0
            && $position->y >= 0
            && $position->x < $this->width
            && $position->y < $this->height;
    }

    public function hasFoodAt(Coordinate $position): bool
    {
        foreach ($this->food as $food) {
            if ($food->equals($position)) {
                return true;
            }
        }
        return false;
    }

    public function hasHazardAt(Coordinate $position): bool
    {
        foreach ($this->hazards as $hazard) {
            if ($hazard->equals($position)) {
                return true;
            }
        }
        return false;
    }

    /** True if any snake on the board occupies this position. */
    public function hasSnakeAt(Coordinate $position): bool
    {
        foreach ($this->snakes as $snake) {
            if ($snake->occupies($position)) {
                return true;
            }
        }
        return false;
    }

    /**
     * True if any snake other than $exclude has a head that could move into
     * this position on its next turn (i.e. the position is adjacent to that
     * snake's head).
     */
    public function isThreatenedFor(Coordinate $position, Battlesnake $exclude): bool
    {
        foreach ($this->snakes as $snake) {
            if ($snake->id === $exclude->id) {
                continue;
            }
            if ($snake->couldReach($position)) {
                return true;
            }
        }
        return false;
    }
}
