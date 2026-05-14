<?php

declare(strict_types=1);

// Suppress deprecations from third-party libraries (Crell\Serde currently triggers
// `array_key_exists(null, ...)` on PHP 8.5). Re-enable once vendor catches up.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require_once __DIR__ . '/../vendor/autoload.php';

use BattleSnake\Core\ApplicationFactory;

new ApplicationFactory()->make()->run();
