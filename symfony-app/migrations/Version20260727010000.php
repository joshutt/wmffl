<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Backfills the two split activation-penalty keys into every season
 * row's scoring_rules JSON, restoring the original seed migration's
 * intent (an explicit per-row snapshot, so future registry default
 * changes never silently alter historical seasons' math).
 *
 * illegal_lineup_penalty was part of that original snapshot (seeded by
 * Version20260718000000) on every environment except this branch's own
 * dev database, where an earlier draft of this work stripped it back
 * out before being reverted. bye_week_lineup_penalty is brand new and
 * has never been stored anywhere. Both backfills use
 * JSON_CONTAINS_PATH to only touch rows where the key is genuinely
 * absent - a stored explicit JSON null means "not awarded that season"
 * and must not be overwritten, and any environment (stage/prod) that
 * already has illegal_lineup_penalty must keep whatever value is there
 * (default or a real historical override) rather than have it
 * clobbered to the default.
 */
final class Version20260727010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Backfill illegal/bye-week lineup penalty keys into scoring_rules where missing';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE seasons
            SET scoring_rules = JSON_SET(scoring_rules, '$.illegal_lineup_penalty', 2)
            WHERE JSON_CONTAINS_PATH(scoring_rules, 'one', '$.illegal_lineup_penalty') = 0
            SQL);

        $this->addSql(<<<'SQL'
            UPDATE seasons
            SET scoring_rules = JSON_SET(scoring_rules, '$.bye_week_lineup_penalty', 2)
            WHERE JSON_CONTAINS_PATH(scoring_rules, 'one', '$.bye_week_lineup_penalty') = 0
            SQL);
    }

    public function down(Schema $schema): void
    {
        // Only bye_week_lineup_penalty is unambiguously introduced by
        // this migration everywhere; illegal_lineup_penalty's presence
        // predates it on most environments, so it's left alone here to
        // avoid stripping data this migration didn't add.
        $this->addSql(<<<'SQL'
            UPDATE seasons
            SET scoring_rules = JSON_REMOVE(scoring_rules, '$.bye_week_lineup_penalty')
            SQL);
    }
}
