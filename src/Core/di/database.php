<?php

declare(strict_types=1);

use BattleSnake\Core\Config;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;

return [
    Connection::class => static function (ContainerInterface $c): Connection {
        $config = $c->get(Config::class);
        $params = (new DsnParser([
            'sqlite' => 'pdo_sqlite',
            'mysql' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
        ]))->parse($config->dsn);

        return DriverManager::getConnection($params);
    },

    EntityManagerInterface::class => static function (ContainerInterface $c): EntityManagerInterface {
        $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
            paths: [\dirname(__DIR__, 2) . '/EventSourcing/Projection'],
            isDevMode: true,
            cache: new Symfony\Component\Cache\Adapter\ArrayAdapter(),
        );
        return new EntityManager($c->get(Connection::class), $ormConfig);
    },
];
