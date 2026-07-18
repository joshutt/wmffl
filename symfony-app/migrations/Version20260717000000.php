<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 10 quicklinks: admin-managed homepage "Other Links" with
 * per-link date windows, replacing the static list hand-edited each
 * season (football/quicklinks.php). Seeds the three links the static
 * template showed (literal current-season URLs — the admin updates the
 * seasonal ones each year) so the homepage widget is unchanged on
 * deploy.
 */
final class Version20260717000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add quicklinks table and seed the three current homepage links';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE quicklinks (
              id int(11) NOT NULL AUTO_INCREMENT,
              label varchar(100) NOT NULL,
              url varchar(255) NOT NULL,
              start_date date DEFAULT NULL,
              end_date date DEFAULT NULL,
              active tinyint(1) NOT NULL DEFAULT 1,
              sort_order int(11) NOT NULL DEFAULT 0,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
            SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO quicklinks (label, url, start_date, end_date, active, sort_order) VALUES
              ('Draft Order', '/history/2026Season/draftorder', NULL, NULL, 1, 1),
              ('Protection Costs', '/history/2026Season/protectioncost', NULL, NULL, 1, 2),
              ('Finances', '/history/teammoney', NULL, NULL, 1, 3)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE quicklinks');
    }
}
