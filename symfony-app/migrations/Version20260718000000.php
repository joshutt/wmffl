<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Season rules foundation: the seasons table holds per-season league
 * rules (schedule structure, finances, scoring parameters) that were
 * previously hardcoded constants. Seeds 1992-2026 with the current
 * (2024+) rules as a starting point; historical seasons are corrected
 * via /admin/seasons as their actual rules are recreated (verified=0
 * marks them unconfirmed).
 *
 * Known historical delta applied here: 60+ yard field goals were worth
 * 10 before the 2024 change to 7 (football/base/scoring2023.php vs
 * scoring2024.php). When FG60=10 actually started is unknown, so it is
 * seeded for every season through 2023 as the best guess.
 *
 * The seed values are a frozen snapshot of ScoringRuleRegistry defaults
 * at the time this migration was written - deliberately not read from
 * the registry so re-running the migration later always produces the
 * same rows.
 */
final class Version20260718000000 extends AbstractMigration
{
    private const FIRST_SEASON = 1992;
    private const LAST_SEASON = 2026;

    private const SCORING_DEFAULTS = [
        'illegal_lineup_penalty' => 2,
        'spec_td' => 12,
        'two_pt' => 2,
        'hc_tie' => 1,
        'hc_win_base' => 3,
        'hc_win_margin_divisor' => 10,
        'hc_penalty_tiers' => [[3, 3], [6, 2], [8, 1], [10, 0], [12, -1], [14, -2], [null, -3]],
        'qb_yards_min' => 200,
        'qb_yards_offset' => 175,
        'qb_yards_divisor' => 25,
        'qb_td' => 6,
        'qb_turnover' => -2,
        'off_yards_min' => 70,
        'off_yards_offset' => 60,
        'off_yards_divisor' => 10,
        'off_rec_min' => 5,
        'off_rec_offset' => 4,
        'off_td' => 6,
        'off_fumble' => -2,
        'te_rec_bonus_min' => 2,
        'te_rec_bonus_max' => 6,
        'te_rec_double_bonus_at' => 4,
        'te_rec_overflow_min' => 12,
        'ol_td' => 1,
        'ol_yards_min' => 100,
        'ol_yards_offset' => 90,
        'ol_yards_divisor' => 10,
        'ol_sack_map' => [5, 2, 1, 0, 0, -1, -2, -5],
        'ol_sack_overflow_offset' => 6,
        'ol_sack_overflow_per' => -5,
        'k_xp' => 1,
        'k_miss_xp' => -1,
        'k_fg30' => 3,
        'k_fg40' => 4,
        'k_fg50' => 5,
        'k_fg60' => 7,
        'k_miss_fg' => -1,
        'def_tackle' => 1,
        'def_pass_defend' => 1,
        'def_sack' => 2,
        'def_sack_bonus_min' => 3,
        'def_sack_bonus_offset' => 2,
        'def_int' => 4,
        'def_fum_rec' => 2,
        'def_force_fum' => 3,
        'def_return_yards_divisor' => 20,
        'def_td' => 9,
        'def_safety' => 6,
        'def_block' => 3,
    ];

    public function getDescription(): string
    {
        return 'Add seasons rule table, seeded 1992-2026 (FG60=10 through 2023)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE seasons (
              season int(11) NOT NULL,
              regular_season_weeks int(11) NOT NULL DEFAULT 14,
              total_weeks int(11) NOT NULL DEFAULT 16,
              max_active_players int(11) NOT NULL DEFAULT 25,
              num_of_games int(11) NOT NULL DEFAULT 84,
              entry_fee decimal(6,2) NOT NULL DEFAULT 75.00,
              illegal_activation_fine decimal(5,2) NOT NULL DEFAULT 5.00,
              bye_week_activation_fine decimal(5,2) NOT NULL DEFAULT 1.00,
              extra_transaction_fine decimal(5,2) NOT NULL DEFAULT 1.00,
              win_percent decimal(5,4) NOT NULL DEFAULT 0.2500,
              post_percent decimal(5,4) NOT NULL DEFAULT 0.5000,
              div_percent decimal(5,4) NOT NULL DEFAULT 0.0500,
              playoff_percent decimal(5,4) NOT NULL DEFAULT 0.0500,
              final_percent decimal(5,4) NOT NULL DEFAULT 0.2500,
              champ_percent decimal(5,4) NOT NULL DEFAULT 0.5000,
              scoring_strategy varchar(32) NOT NULL DEFAULT 'standard',
              scoring_rules longtext NOT NULL,
              verified tinyint(1) NOT NULL DEFAULT 0,
              notes text DEFAULT NULL,
              PRIMARY KEY (season)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
            SQL);

        for ($season = self::FIRST_SEASON; $season <= self::LAST_SEASON; $season++) {
            $rules = self::SCORING_DEFAULTS;
            if ($season <= 2023) {
                $rules['k_fg60'] = 10;
            }

            $this->addSql(
                'INSERT INTO seasons (season, scoring_rules, verified) VALUES (:season, :rules, :verified)',
                [
                    'season' => $season,
                    'rules' => json_encode($rules),
                    'verified' => $season >= 2024 ? 1 : 0,
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE seasons');
    }
}
