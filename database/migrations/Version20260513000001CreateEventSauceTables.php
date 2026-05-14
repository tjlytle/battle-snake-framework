<?php

declare(strict_types=1);

namespace BattleSnake\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260513000001CreateEventSauceTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the EventSauce domain_messages table (event store).';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('domain_messages');
        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
            'notnull' => true,
        ]);
        $table->addColumn('event_id', 'string', ['length' => 36, 'notnull' => true]);
        $table->addColumn('aggregate_root_id', 'string', ['length' => 36, 'notnull' => true]);
        $table->addColumn('version', 'integer', ['notnull' => true]);
        $table->addColumn('payload', 'text', ['notnull' => true]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create(),
        );
        $table->addUniqueIndex(['aggregate_root_id', 'version'], 'uniq_aggregate_version');
        $table->addIndex(['aggregate_root_id'], 'idx_aggregate_root_id');
        $table->addIndex(['event_id'], 'idx_event_id');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('domain_messages');
    }
}
