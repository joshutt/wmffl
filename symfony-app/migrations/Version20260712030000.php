<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 6 table cleanup, schema audit: drop the scratch tables
 * `tmp_players` (566 rows) and `tmp_scan` (611 rows). No code
 * references either one (only the schema.sql dump mentions them);
 * they are leftovers from a one-off import/scan session.
 */
final class Version20260712030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop orphaned scratch tables tmp_players and tmp_scan';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE tmp_players');
        $this->addSql('DROP TABLE tmp_scan');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'tmp_players/tmp_scan were one-off scratch tables; restore from backup if needed'
        );
    }
}
