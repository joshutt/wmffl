<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 6 table cleanup: drop the legacy `players` table (6,302 rows).
 * Superseded by `newplayers` (15,743 rows) — every players.PlayerID
 * exists in newplayers.playerid, names match modulo spelling
 * normalizations. The last readers (six frozen 2003-2008 history
 * pages and the orphaned public/images/players/playerstats.php) have
 * been ported to newplayers or deleted.
 */
final class Version20260712010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop legacy players table (superseded by newplayers)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE players');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'players was a pre-2009 snapshot of newplayers; restore from backup if needed'
        );
    }
}
