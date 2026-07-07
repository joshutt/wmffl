<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Last-edited tracking for published articles: NULL until an already-active
 * article is saved again (member edit flow or admin form), then now() on
 * every such save. Nothing outside the Symfony app reads this column.
 */
final class Version20260706010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nullable lastEdited DATETIME to articles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles ADD lastEdited DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles DROP COLUMN lastEdited');
    }
}
