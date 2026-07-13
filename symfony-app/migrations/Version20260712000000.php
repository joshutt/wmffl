<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 6 table cleanup: drop the legacy `activations` table (wide
 * format, one row per team-week, 2001-2008). Fully superseded by
 * `revisedactivations` (tall format, 2001-2025) — verified row-for-row
 * for the 2005-2006 seasons the last two readers displayed, and those
 * readers (the 2005/2006 history boxscore pages) now join
 * `revisedactivations`.
 */
final class Version20260712000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop legacy activations table (superseded by revisedactivations)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE activations');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'activations held pre-2009 wide-format activation data; restore from backup if needed'
        );
    }
}
