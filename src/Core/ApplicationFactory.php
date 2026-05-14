<?php

declare(strict_types=1);

namespace BattleSnake\Core;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * Builds the DI container from the definition files in src/Core/di/ and
 * returns an Application that uses it.
 *
 * Tests can pass extra definition overlays; the last array wins on conflicts.
 */
final class ApplicationFactory
{
    private const DEFAULT_DEFINITIONS = [
        __DIR__ . '/di/core.php',
        __DIR__ . '/di/database.php',
        __DIR__ . '/di/eventsourcing.php',
        __DIR__ . '/di/handlers.php',
        __DIR__ . '/di/snake.php',
    ];

    /**
     * @param list<string|array<string, mixed>> $extra Additional DI definition files or arrays
     */
    public function make(array $extra = []): Application
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);

        foreach (self::DEFAULT_DEFINITIONS as $file) {
            $builder->addDefinitions($file);
        }

        foreach ($extra as $overlay) {
            $builder->addDefinitions($overlay);
        }

        return new Application($builder->build());
    }

    public function makeContainer(array $extra = []): ContainerInterface
    {
        return $this->make($extra)->getContainer();
    }
}
