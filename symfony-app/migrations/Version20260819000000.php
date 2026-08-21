<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drops the dead `blogaddress` column from `user` (Phase 14 admin user
 * management). Confirmed unused anywhere in symfony-app/, football/, or
 * scripts/ before removing it from App\Entity\User.
 */
final class Version20260819000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop unused user.blogaddress column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP COLUMN blogaddress');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD blogaddress varchar(75) DEFAULT NULL');
    }
}
