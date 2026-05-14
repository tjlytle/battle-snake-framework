<?php

declare(strict_types=1);

use BattleSnake\Core\Config;

// SQLite absolute paths require 4 slashes after the scheme: sqlite:////full/path/file.db
$default = 'sqlite:///' . dirname(__DIR__) . '/var/snake.db';

return new Config(
    dsn: $_ENV['SNAKE_DSN'] ?? getenv('SNAKE_DSN') ?: $default,
);
