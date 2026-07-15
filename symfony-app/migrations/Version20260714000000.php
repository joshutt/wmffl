<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 7: reclaim the canonical table names freed by Phase 6's drops.
 *
 *  - revisedactivations -> activations
 *  - newplayers         -> players
 *  - newinjuries        -> injuries
 *
 * The single RENAME TABLE statement is atomic; it re-points the two
 * foreign keys on injuries and ir automatically but leaves their
 * constraint *names* stale, so each is dropped and re-added under its
 * canonical name (MySQL/MariaDB cannot rename a constraint in place).
 * Pure rename — no column, index, or data changes.
 */
final class Version20260714000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename revisedactivations/newplayers/newinjuries to their canonical short names';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            RENAME TABLE revisedactivations TO activations,
                         newplayers TO players,
                         newinjuries TO injuries
            SQL);
        $this->addSql('ALTER TABLE injuries DROP FOREIGN KEY FK_newinjuries_newplayers');
        $this->addSql('ALTER TABLE injuries ADD CONSTRAINT FK_injuries_players FOREIGN KEY (playerid) REFERENCES players (playerid)');
        $this->addSql('ALTER TABLE ir DROP FOREIGN KEY ir_newplayers_playerid_fk');
        $this->addSql('ALTER TABLE ir ADD CONSTRAINT ir_players_playerid_fk FOREIGN KEY (playerid) REFERENCES players (playerid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ir DROP FOREIGN KEY ir_players_playerid_fk');
        $this->addSql('ALTER TABLE injuries DROP FOREIGN KEY FK_injuries_players');
        $this->addSql(<<<'SQL'
            RENAME TABLE activations TO revisedactivations,
                         players TO newplayers,
                         injuries TO newinjuries
            SQL);
        $this->addSql('ALTER TABLE newinjuries ADD CONSTRAINT FK_newinjuries_newplayers FOREIGN KEY (playerid) REFERENCES newplayers (playerid)');
        $this->addSql('ALTER TABLE ir ADD CONSTRAINT ir_newplayers_playerid_fk FOREIGN KEY (playerid) REFERENCES newplayers (playerid)');
    }
}
