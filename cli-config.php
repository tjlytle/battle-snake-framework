<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use BattleSnake\Core\ApplicationFactory;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\DependencyFactory;

$connection = (new ApplicationFactory())->makeContainer()->get(Connection::class);

return DependencyFactory::fromConnection(
    new ConfigurationArray(require __DIR__ . '/migrations.php'),
    new ExistingConnection($connection),
);
