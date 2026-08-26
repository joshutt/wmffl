<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Widens articles.articleText from TEXT (64KB) to MEDIUMTEXT (16MB). The
 * WYSIWYG editor can emit enough markup on a long, image-heavy article to
 * blow past the 65,535-byte TEXT cap; saving then truncates silently at
 * the DB layer.
 */
final class Version20260826000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Widen articles.articleText from TEXT to MEDIUMTEXT';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles MODIFY articleText MEDIUMTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles MODIFY articleText TEXT DEFAULT NULL');
    }
}
