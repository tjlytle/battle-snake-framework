<?php

declare(strict_types=1);

return [
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
    ],
    'migrations_paths' => [
        'BattleSnake\Migrations' => 'database/migrations',
    ],
    'all_or_nothing' => true,
    'transactional' => true,
    'check_database_platform' => false,
];
