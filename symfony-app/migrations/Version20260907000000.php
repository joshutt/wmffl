<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * 2026 rule changes, part 1: the 1-player IR limit is removed. The
 * mechanism that enforced it (RosterMoveService::getTotalRoster() =
 * maxActivePlayers + 1) becomes a separately configurable per-season
 * `max_ir_slots` value instead of a hardcoded "+1".
 *
 * `max_ir_slots` is literal, not a sentinel: 0 means "zero extra slots
 * above the active limit" (as restrictive as no-IR-at-all), not
 * "unlimited". For 2026, Josh wants IR effectively unlimited, which is
 * expressed here as a large explicit number (999) rather than special
 * casing zero in the check logic.
 *
 * Historical values below are Josh's explicit direction for
 * record-keeping purposes, not a literal reconstruction of what the old
 * hardcoded "+1" rule actually enforced at the time - since
 * getTotalRoster() is only ever evaluated against the current
 * transactional season, these historical rows are inert. Every other
 * season (2019 and earlier) is left at the column default of 0.
 */
final class Version20260907000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add seasons.max_ir_slots (2026 rule change: IR limit removed)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE seasons ADD max_ir_slots INT NOT NULL DEFAULT 0');

        // 2026: effectively unlimited IR (adjust via /admin/seasons if a
        // different cap is preferred).
        $this->addSql('UPDATE seasons SET max_ir_slots = 999 WHERE season = 2026');

        // 2024-2025: 1 extra slot above the active limit (matches the
        // old hardcoded "+1" rule for the most recent seasons it applied to).
        $this->addSql('UPDATE seasons SET max_ir_slots = 1 WHERE season IN (2024, 2025)');

        // 2022-2023: 3, per Josh.
        $this->addSql('UPDATE seasons SET max_ir_slots = 3 WHERE season IN (2022, 2023)');

        // 2020-2021: 99, per Josh.
        $this->addSql('UPDATE seasons SET max_ir_slots = 99 WHERE season IN (2020, 2021)');

        // 2019 and earlier: left at the column default of 0.
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE seasons DROP COLUMN max_ir_slots');
    }
}
