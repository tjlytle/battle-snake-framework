<?php

declare(strict_types=1);

// Shared bootstrap for test runners (PHPUnit, Behat) and CLI tools (Doctrine Migrations).
// Production entry (public/index.php) sets the same up directly.
//
// Crell\Serde 1.5 triggers PHP 8.5's `array_key_exists(null, ...)` deprecation.
// Drop the error_reporting override once that's fixed upstream.

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require_once __DIR__ . '/vendor/autoload.php';
