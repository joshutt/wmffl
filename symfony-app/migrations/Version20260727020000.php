<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rule Proposals redesign (Phase 10.6): turns the thin `issues` table
 * into a proper proposal record and wires up the ballot.
 *
 *  - `issues`: widen IssueName; add Rationale, RuleChangeText (markdown),
 *    a Published approval gate and a Status voting-lifecycle enum
 *    (backfilled from the free-text Result before Result is dropped);
 *    drop the single-int Sponsor (replaced by issue_sponsors).
 *  - `issue_sponsors`: ordered co-sponsors, FK to issues + user.
 *  - `seasons`: per-season ballot pass/fail thresholds (were hardcoded
 *    .67/.51 in ballotcount.php), era-seeded (.67 pre-2022, .51 from 2022).
 *  - `ballot`: the missing FK constraints to issues and team.
 *
 * Status mapping from the legacy Result varchar: the spec anticipated
 * only PASS/Passed/REJECT/REJECTED/typos/null, but the live data also
 * carries FAIL (a rejected outcome) and WITHDRAWN, so those map to their
 * obvious enum values rather than falling through to Open (which would
 * wrongly resurrect ~46 settled proposals). Everything else (null, the
 * lone "Joel" typo) is Open, per the spec's default.
 */
final class Version20260727020000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rule Proposals: issues columns + Status/Published, issue_sponsors, season thresholds, ballot FKs';
    }

    public function up(Schema $schema): void
    {
        // --- ballot orphan pre-check: never silently delete (spec) ---
        $orphanIssues = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ballot b LEFT JOIN issues i ON b.IssueID = i.IssueID WHERE i.IssueID IS NULL'
        );
        $orphanTeams = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ballot b LEFT JOIN team t ON b.TeamID = t.TeamID WHERE t.TeamID IS NULL'
        );
        $this->abortIf(
            $orphanIssues > 0 || $orphanTeams > 0,
            "Aborting: ballot has $orphanIssues orphan issue rows and $orphanTeams orphan team rows. "
            . 'Resolve these with Josh before adding FK constraints (do not delete blindly).'
        );

        // --- issues: widen + new columns ---
        $this->addSql("ALTER TABLE issues MODIFY IssueName varchar(120) NOT NULL");
        $this->addSql('ALTER TABLE issues ADD Rationale text DEFAULT NULL');
        $this->addSql('ALTER TABLE issues ADD RuleChangeText text DEFAULT NULL');
        $this->addSql('ALTER TABLE issues ADD Published tinyint(1) NOT NULL DEFAULT 0');
        $this->addSql(
            "ALTER TABLE issues ADD Status enum('Open','Passed','Rejected','Withdrawn') NOT NULL DEFAULT 'Open'"
        );

        // Backfill Status from the legacy Result before dropping it.
        $this->addSql("UPDATE issues SET Status = 'Passed'   WHERE Result IN ('PASS', 'Passed')");
        $this->addSql("UPDATE issues SET Status = 'Rejected' WHERE Result IN ('REJECT', 'REJECTED', 'Rejected', 'FAIL')");
        $this->addSql("UPDATE issues SET Status = 'Withdrawn' WHERE Result = 'WITHDRAWN'");
        // Everything else (null, typos) keeps the DEFAULT 'Open'.

        // Existing rows were already public on the hand-written pages.
        $this->addSql('UPDATE issues SET Published = 1');

        $this->addSql('ALTER TABLE issues DROP COLUMN Result');
        $this->addSql('ALTER TABLE issues DROP COLUMN Sponsor');

        // --- issue_sponsors: ordered co-sponsors ---
        $this->addSql(<<<'SQL'
            CREATE TABLE issue_sponsors (
              IssueID int(11) NOT NULL,
              UserID int(11) NOT NULL,
              SortOrder int(11) NOT NULL DEFAULT 0,
              PRIMARY KEY (IssueID, UserID),
              KEY IDX_issue_sponsors_user (UserID),
              CONSTRAINT FK_issue_sponsors_issue FOREIGN KEY (IssueID) REFERENCES issues (IssueID) ON DELETE CASCADE,
              CONSTRAINT FK_issue_sponsors_user FOREIGN KEY (UserID) REFERENCES user (UserID)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
            SQL);

        // --- seasons: per-season ballot thresholds ---
        $this->addSql(
            'ALTER TABLE seasons ADD proposal_pass_threshold decimal(5,4) NOT NULL DEFAULT 0.5100'
        );
        $this->addSql(
            'ALTER TABLE seasons ADD proposal_fail_threshold decimal(5,4) NOT NULL DEFAULT 0.5100'
        );
        // The league lowered the pass bar from .67 to .51 starting in 2022.
        $this->addSql('UPDATE seasons SET proposal_pass_threshold = 0.6700 WHERE season < 2022');

        // --- ballot: the FK constraints legacy never had ---
        $this->addSql(
            'ALTER TABLE ballot ADD CONSTRAINT FK_ballot_issue FOREIGN KEY (IssueID) REFERENCES issues (IssueID)'
        );
        $this->addSql(
            'ALTER TABLE ballot ADD CONSTRAINT FK_ballot_team FOREIGN KEY (TeamID) REFERENCES team (TeamID)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ballot DROP FOREIGN KEY FK_ballot_issue');
        $this->addSql('ALTER TABLE ballot DROP FOREIGN KEY FK_ballot_team');

        $this->addSql('ALTER TABLE seasons DROP COLUMN proposal_pass_threshold');
        $this->addSql('ALTER TABLE seasons DROP COLUMN proposal_fail_threshold');

        $this->addSql('DROP TABLE issue_sponsors');

        // Restore the pre-redesign issues shape. Result/Sponsor content is
        // not recoverable (Status/issue_sponsors superseded them); down()
        // only restores the columns so the schema round-trips.
        $this->addSql('ALTER TABLE issues ADD Sponsor int(11) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE issues ADD Result varchar(10) DEFAULT NULL');
        $this->addSql("UPDATE issues SET Result = 'PASS'   WHERE Status = 'Passed'");
        $this->addSql("UPDATE issues SET Result = 'REJECT' WHERE Status = 'Rejected'");
        $this->addSql("UPDATE issues SET Result = 'WITHDRAWN' WHERE Status = 'Withdrawn'");
        $this->addSql('ALTER TABLE issues DROP COLUMN Status');
        $this->addSql('ALTER TABLE issues DROP COLUMN Published');
        $this->addSql('ALTER TABLE issues DROP COLUMN RuleChangeText');
        $this->addSql('ALTER TABLE issues DROP COLUMN Rationale');
        $this->addSql('ALTER TABLE issues MODIFY IssueName varchar(40) NOT NULL');
    }
}
