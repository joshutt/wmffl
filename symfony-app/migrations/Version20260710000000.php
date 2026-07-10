<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seed the protections deadline config key. Legacy protections.php
 * hard-coded the date (last: 2025-08-17 00:05 EDT, effectively
 * 2025-08-16 23:59 after its six-minute adjustment) and needed a code
 * edit each year; the Symfony page reads config `protections.deadline`
 * instead. Seeded to the 2026 analog — the Sunday-boundary a week
 * before the draft weekend — and adjustable per season with a simple
 * UPDATE on the config table.
 */
final class Version20260710000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed the data-driven protections.deadline config value';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO config (`key`, `value`) VALUES ('protections.deadline', '2026-08-16 23:59:00')
             ON DUPLICATE KEY UPDATE `value` = `value`"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM config WHERE `key` = 'protections.deadline'");
    }
}
