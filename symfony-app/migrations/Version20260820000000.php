<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 15: lineup limits become per-season data. `seasons.lineup_rules`
 * holds the position min/max pairs and the flex constraint that used to
 * be inline sizeof() checks in football/activate/processActivations.php.
 *
 * Every existing season is seeded with the current limits (1 HC, 1 QB,
 * 1-2 RB, 2-3 WR, 1-2 TE with RB+WR+TE = 5, 1 K, 1 OL, 2 DL, 2 LB,
 * 2 DB). Older seasons almost certainly differed; those get corrected
 * through /admin/seasons the same way scoring rules do, and `verified`
 * is deliberately left alone here.
 *
 * The seed is a frozen snapshot of LineupRuleRegistry defaults, not a
 * read of the registry, so re-running this migration always produces
 * the same rows.
 */
final class Version20260820000000 extends AbstractMigration
{
    private const LINEUP_DEFAULTS = [
        'HC' => ['min' => 1, 'max' => 1],
        'QB' => ['min' => 1, 'max' => 1],
        'RB' => ['min' => 1, 'max' => 2],
        'WR' => ['min' => 2, 'max' => 3],
        'TE' => ['min' => 1, 'max' => 2],
        'K'  => ['min' => 1, 'max' => 1],
        'OL' => ['min' => 1, 'max' => 1],
        'DL' => ['min' => 2, 'max' => 2],
        'LB' => ['min' => 2, 'max' => 2],
        'DB' => ['min' => 2, 'max' => 2],
        'flex_group' => ['RB', 'WR', 'TE'],
        'flex_total' => 5,
    ];

    public function getDescription(): string
    {
        return 'Add seasons.lineup_rules, seeded with the current position limits';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE seasons ADD lineup_rules longtext NOT NULL');
        $this->addSql('UPDATE seasons SET lineup_rules = :rules', ['rules' => json_encode(self::LINEUP_DEFAULTS)]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE seasons DROP COLUMN lineup_rules');
    }
}
