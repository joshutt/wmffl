<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Data fix: one legacy user row holds '' in the active column, which is not
 * a valid backing value for App\Enum\ActiveEnum ('Y'/'N') and makes any
 * query that hydrates User entities throw a ValueError.
 */
final class Version20260706000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Normalize empty user.active values to 'N' so User entities hydrate into ActiveEnum";
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE user SET active = 'N' WHERE active = ''");
    }

    public function down(Schema $schema): void
    {
        // The original '' value carried no information worth restoring
    }
}
