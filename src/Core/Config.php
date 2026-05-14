<?php

declare(strict_types=1);

namespace BattleSnake\Core;

final readonly class Config
{
    public function __construct(public string $dsn)
    {
    }
}
