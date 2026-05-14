<?php

declare(strict_types=1);

/**
 * To swap in your own snake:
 *   1. Create a class under src/Snake/ that implements BattleSnake\Strategy\Strategy.
 *   2. Change the binding below to point at it.
 */

use BattleSnake\Snake\RandomSnake;
use BattleSnake\Strategy\Decorator\Nudgeable;
use BattleSnake\Strategy\Strategy;

return [
    // Default: plain RandomSnake. Replace with `get(YourSnake::class)` to swap in.
    Strategy::class => DI\get(Nudgeable::class),

    // The Nudgeable decorator wraps a fallback. Swap the fallback to change behavior.
    Nudgeable::class => DI\autowire()
        ->constructorParameter('fallback', DI\get(RandomSnake::class)),

    RandomSnake::class => DI\autowire(),
];
