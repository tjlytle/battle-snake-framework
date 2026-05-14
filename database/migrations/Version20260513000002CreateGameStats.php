<?php

declare(strict_types=1);

namespace BattleSnake\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260513000002CreateGameStats extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the game_stats projection table.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('game_stats');
        $table->addColumn('aggregate_id', 'string', ['length' => 36, 'notnull' => true]);
        $table->addColumn('win', 'boolean', ['notnull' => true]);
        $table->addColumn('length', 'integer', ['notnull' => true]);
        $table->addColumn('survived', 'integer', ['notnull' => true]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('aggregate_id')
                ->create(),
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('game_stats');
    }
}
