<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 6 table cleanup: merge the legacy `injuries` table into
 * `newinjuries`, then drop it.
 *
 * The two tables are complementary, not duplicates: `injuries` holds
 * 2010-2019 (73,482 rows, single-letter status enum), `newinjuries`
 * holds 2020-2025 (56,975 rows, word statuses). Verified before
 * writing this migration: zero orphan playerids, zero overlapping
 * (season, week, playerid) keys, old `details` max length 39.
 *
 * Conversions on the way in:
 * - `newinjuries.details` widened varchar(32) -> varchar(50) to match
 *   the old column (8,963 old rows exceed 32 chars);
 * - status letters mapped to the word vocabulary used by the live
 *   table and InjuredReserveService::IR_STATUSES:
 *   P->Probable, Q->Questionable, D->Doubtful, O->Out, I->IR,
 *   S->Suspended.
 */
final class Version20260712020000 extends AbstractMigration
{
    private const OLD_ROWS = 73482;

    public function getDescription(): string
    {
        return 'Merge legacy injuries (2010-2019) into newinjuries, then drop it';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE newinjuries MODIFY details VARCHAR(50) DEFAULT NULL');

        $this->addSql(
            "INSERT INTO newinjuries (playerid, season, week, status, details)
             SELECT playerid, season, week,
                    CASE status
                        WHEN 'P' THEN 'Probable'
                        WHEN 'Q' THEN 'Questionable'
                        WHEN 'D' THEN 'Doubtful'
                        WHEN 'O' THEN 'Out'
                        WHEN 'I' THEN 'IR'
                        WHEN 'S' THEN 'Suspended'
                    END,
                    details
             FROM injuries"
        );

        $this->addSql('DROP TABLE injuries');
    }

    public function postUp(Schema $schema): void
    {
        $merged = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM newinjuries WHERE season BETWEEN 2010 AND 2019'
        );
        if ($merged !== self::OLD_ROWS) {
            throw new \RuntimeException(sprintf(
                'injuries merge expected %d rows for 2010-2019 in newinjuries, found %d',
                self::OLD_ROWS,
                $merged
            ));
        }
        $unmapped = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM newinjuries WHERE status IS NULL OR status = ''"
        );
        if ($unmapped !== 0) {
            throw new \RuntimeException(sprintf(
                'injuries merge left %d rows with an unmapped status',
                $unmapped
            ));
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'injuries (2010-2019) was merged into newinjuries; restore from backup if needed'
        );
    }
}
