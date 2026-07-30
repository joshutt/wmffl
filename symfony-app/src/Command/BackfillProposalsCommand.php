<?php

namespace App\Command;

use App\Enum\IssueStatus;
use App\Service\Backfill\DbSponsorLookup;
use App\Service\Backfill\ParsedProposal;
use App\Service\Backfill\ProposalPageParser;
use App\Service\Backfill\SponsorResolver;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * One-time backfill of historical rule proposals from the hand-written
 * football/rules/proposals{year}.php pages into issues / issue_sponsors.
 *
 * It only *generates* reviewable output — an idempotent SQL file and a
 * human-readable report — and never touches the database itself. Per the
 * spec, Josh reviews the report and applies the SQL at deploy, separate
 * from the code-merge gate. Reconciliation enriches only empty fields and
 * never overwrites a non-empty value (conflicts are reported instead);
 * sponsors that can't be cleanly resolved are flagged, never guessed.
 *
 * The source pages are retired from the live football/rules/ path this
 * phase but preserved under archive/proposals/, which is this command's
 * default --rules-dir, so it stays re-runnable after merge.
 */
#[AsCommand(name: 'app:backfill-proposals', description: 'Generate the issues backfill SQL + report from legacy proposal pages')]
class BackfillProposalsCommand extends Command
{
    /**
     * Manual sponsor resolutions from Josh for page strings the automatic
     * rules can't reach (nicknames, defunct/co-sponsor team strings). Keyed
     * "IssueNum|raw page string" => ordered list of user names; each name is
     * still resolved through the normal user lookup, so a rename in `user`
     * never silently links the wrong person.
     *
     * @var array<string, string[]>
     */
    private const SPONSOR_OVERRIDES = [
        '2005.7|Illuminati' => ['Tim Shoobridge'],
        '2005.8a|Illuminati' => ['Tim Shoobridge'],
        '2010.7|Lindbergh Baby Casserole' => ['Tim Shoobridge'],
        '2013.2|MeggaMen with Werewolves' => ['Tom Marsh', 'Josh Utterback'],
        '2025.1|Thomas Marsh' => ['Tom Marsh'],
    ];

    /**
     * Josh's manual resolutions of reported Status conflicts: force-set the
     * value regardless of what's stored, and don't re-flag it. Keyed by
     * IssueNum.
     *
     * @var array<string, IssueStatus>
     */
    private const STATUS_OVERRIDES = [
        // Page said Withdrawn, DB had Rejected; Josh confirms Withdrawn.
        '2017.4' => IssueStatus::Withdrawn,
    ];

    public function __construct(
        private Connection $conn,
        private ProposalPageParser $parser,
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('rules-dir', null, InputOption::VALUE_REQUIRED, 'Directory of proposals*.php pages')
            ->addOption('sql', null, InputOption::VALUE_REQUIRED, 'Output SQL path')
            ->addOption('report', null, InputOption::VALUE_REQUIRED, 'Output report path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rulesDir = $input->getOption('rules-dir') ?: $this->projectDir . '/../archive/proposals';
        $sqlPath = $input->getOption('sql')
            ?: $this->projectDir . '/../scripts/database/migration/2026-07-27-issues-backfill.sql';
        $reportPath = $input->getOption('report')
            ?: $this->projectDir . '/../scripts/database/migration/2026-07-27-issues-backfill-report.md';

        $files = glob(rtrim($rulesDir, '/') . '/proposals*.php') ?: [];
        if ($files === []) {
            $io->error("No proposals*.php files found in $rulesDir");

            return Command::FAILURE;
        }

        $resolver = new SponsorResolver(new DbSponsorLookup($this->conn));

        $sql = [];
        $inserted = [];   // season => count
        $enriched = [];   // season => count
        $unresolvedSponsors = [];
        $conflicts = [];

        foreach ($files as $file) {
            $html = file_get_contents($file) ?: '';
            $fileSeason = $this->seasonFromFilename($file);
            $proposals = $this->parser->parse($html, $fileSeason ?? 0);

            foreach ($proposals as $p) {
                $season = $this->proposalSeason($p, $fileSeason);
                $existing = $this->findExisting($p->issueNum, $season);

                if ($existing === null) {
                    $this->emitInsert($sql, $p, $season);
                    $inserted[$season] = ($inserted[$season] ?? 0) + 1;
                } else {
                    $this->emitEnrich($sql, $p, $season, $existing, $conflicts, basename($file));
                    $enriched[$season] = ($enriched[$season] ?? 0) + 1;
                }

                // Sponsors: enrich only issues that have no sponsors yet
                // (decided here, at generation time, against the live DB) so
                // we never add to a manually-curated set. The emitted SQL is
                // then guarded per (issue,user) pair, which keeps re-runs
                // idempotent AND lets co-sponsors of the same issue coexist
                // (a blanket "no sponsors" guard would block the 2nd sponsor).
                if ($existing !== null && $this->hasSponsors((int) $existing['IssueID'])) {
                    continue;
                }
                $order = 0;
                foreach ($p->sponsorNames as $rawName) {
                    // Josh's manual resolutions for names the automatic rules
                    // can't reach (nicknames, defunct team names, co-sponsor
                    // strings) take precedence and expand to one or more users.
                    $overrideNames = self::SPONSOR_OVERRIDES[$p->issueNum . '|' . $rawName] ?? null;
                    if ($overrideNames !== null) {
                        foreach ($overrideNames as $overrideName) {
                            $resolved = $resolver->resolve($overrideName, $season);
                            if ($resolved->isResolved()) {
                                $this->emitSponsor($sql, $p->issueNum, $season, $resolved->userId, $order++);
                            } else {
                                $unresolvedSponsors[] = $this->flagRow($file, $p, $overrideName, 'manual override "' . $overrideName . '" did not resolve: ' . $resolved->flagReason);
                            }
                        }
                        continue;
                    }

                    $resolved = $resolver->resolve($rawName, $season);
                    if ($resolved->isResolved()) {
                        $this->emitSponsor($sql, $p->issueNum, $season, $resolved->userId, $order++);
                    } else {
                        $unresolvedSponsors[] = $this->flagRow($file, $p, $rawName, $resolved->flagReason ?? '');
                    }
                }
            }
        }

        file_put_contents($sqlPath, $this->renderSql($sql));
        file_put_contents($reportPath, $this->renderReport($inserted, $enriched, $unresolvedSponsors, $conflicts));

        $io->success(sprintf(
            'Wrote %d SQL statements to %s and the report to %s',
            count($sql),
            $sqlPath,
            $reportPath
        ));
        $io->writeln(sprintf(
            'Inserted: %d  Enriched: %d  Unresolved sponsors: %d  Conflicts: %d',
            array_sum($inserted),
            array_sum($enriched),
            count($unresolvedSponsors),
            count($conflicts)
        ));
        $io->warning('Review the report before applying the SQL — it is NOT applied automatically.');

        return Command::SUCCESS;
    }

    private function flagRow(string $file, ParsedProposal $p, string $raw, string $reason): array
    {
        return [
            'file' => basename($file),
            'issueNum' => $p->issueNum,
            'issueName' => $p->issueName,
            'raw' => $raw,
            'reason' => $reason,
        ];
    }

    private function hasSponsors(int $issueId): bool
    {
        return (int) $this->conn->fetchOne(
            'SELECT COUNT(*) FROM issue_sponsors WHERE IssueID = :id',
            ['id' => $issueId]
        ) > 0;
    }

    private function findExisting(string $issueNum, int $season): ?array
    {
        $row = $this->conn->fetchAssociative(
            'SELECT IssueID, Rationale, RuleChangeText, Status FROM issues WHERE IssueNum = :num AND Season = :season',
            ['num' => $issueNum, 'season' => $season]
        );

        return $row === false ? null : $row;
    }

    private function emitInsert(array &$sql, ParsedProposal $p, int $season): void
    {
        $sql[] = sprintf(
            "INSERT INTO issues (IssueNum, IssueName, Season, Status, Rationale, RuleChangeText, Published)\n"
            . "SELECT %s, %s, %d, %s, %s, %s, 1\n"
            . "WHERE NOT EXISTS (SELECT 1 FROM issues WHERE IssueNum = %s AND Season = %d);",
            $this->q($p->issueNum),
            $this->q($p->issueName),
            $season,
            $this->q($p->status->value),
            $this->qNull($p->rationaleMarkdown),
            $this->qNull($p->ruleChangeMarkdown),
            $this->q($p->issueNum),
            $season,
        );
    }

    private function emitEnrich(
        array &$sql,
        ParsedProposal $p,
        int $season,
        array $existing,
        array &$conflicts,
        string $file,
    ): void {
        // Rationale / RuleChangeText: fill only when empty; conflict otherwise.
        foreach ([
            'Rationale' => $p->rationaleMarkdown,
            'RuleChangeText' => $p->ruleChangeMarkdown,
        ] as $col => $value) {
            if ($value === null) {
                continue;
            }
            $current = $existing[$col] ?? null;
            if ($current === null || trim((string) $current) === '') {
                $sql[] = sprintf(
                    "UPDATE issues SET %s = %s WHERE IssueNum = %s AND Season = %d AND (%s IS NULL OR %s = '');",
                    $col,
                    $this->q($value),
                    $this->q($p->issueNum),
                    $season,
                    $col,
                    $col,
                );
            } elseif ($this->normalise($current) !== $this->normalise($value)) {
                $conflicts[] = [
                    'file' => $file,
                    'issueNum' => $p->issueNum,
                    'field' => $col,
                    'existing' => $this->truncate($current),
                    'page' => $this->truncate($value),
                ];
            }
        }

        // Status: an explicit override from Josh resolves a reported
        // conflict by force-setting the value (idempotent), and is neither
        // gated on the current value nor re-reported as a conflict.
        $currentStatus = $existing['Status'] ?? IssueStatus::Open->value;
        $statusOverride = self::STATUS_OVERRIDES[$p->issueNum] ?? null;
        if ($statusOverride !== null) {
            if ($currentStatus !== $statusOverride->value) {
                $sql[] = sprintf(
                    "UPDATE issues SET Status = %s WHERE IssueNum = %s AND Season = %d;",
                    $this->q($statusOverride->value),
                    $this->q($p->issueNum),
                    $season,
                );
            }

            return;
        }

        // Otherwise: only fill when the row is still Open; a decided row that
        // disagrees with the page is a conflict, never overwritten.
        if ($p->status !== IssueStatus::Open) {
            if ($currentStatus === IssueStatus::Open->value) {
                $sql[] = sprintf(
                    "UPDATE issues SET Status = %s WHERE IssueNum = %s AND Season = %d AND Status = 'Open';",
                    $this->q($p->status->value),
                    $this->q($p->issueNum),
                    $season,
                );
            } elseif ($currentStatus !== $p->status->value) {
                $conflicts[] = [
                    'file' => $file,
                    'issueNum' => $p->issueNum,
                    'field' => 'Status',
                    'existing' => $currentStatus,
                    'page' => $p->status->value,
                ];
            }
        }
    }

    private function emitSponsor(array &$sql, string $issueNum, int $season, int $userId, int $order): void
    {
        // Keyed by IssueNum+Season so it works whether the issue was just
        // inserted or already existed; guarded per (issue,user) pair so
        // re-runs never duplicate a row while co-sponsors added in the same
        // run still each go in. ("Enrich only empty" is enforced upstream by
        // skipping issues that already had sponsors at generation time.)
        $sql[] = sprintf(
            "INSERT INTO issue_sponsors (IssueID, UserID, SortOrder)\n"
            . "SELECT i.IssueID, %d, %d FROM issues i\n"
            . "WHERE i.IssueNum = %s AND i.Season = %d\n"
            . "  AND NOT EXISTS (SELECT 1 FROM issue_sponsors s WHERE s.IssueID = i.IssueID AND s.UserID = %d);",
            $userId,
            $order,
            $this->q($issueNum),
            $season,
            $userId,
        );
    }

    private function renderSql(array $statements): string
    {
        $header = "-- Historical rule-proposal backfill (Phase 10.6)\n"
            . "-- Generated by app:backfill-proposals. Idempotent: enriches only\n"
            . "-- empty fields, never overwrites a non-empty value, never duplicates\n"
            . "-- a sponsor. Review the accompanying report before applying.\n\n";

        return $header . implode("\n\n", $statements) . "\n";
    }

    private function renderReport(array $inserted, array $enriched, array $unresolved, array $conflicts): string
    {
        $out = "# Rule-proposal backfill report\n\n";
        $out .= "Generated " . date('Y-m-d H:i') . " by `app:backfill-proposals`.\n\n";

        $out .= "## Rows inserted vs enriched (by season)\n\n";
        $seasons = array_unique(array_merge(array_keys($inserted), array_keys($enriched)));
        sort($seasons);
        $out .= "| Season | Inserted | Enriched |\n|---|---|---|\n";
        foreach ($seasons as $s) {
            $out .= sprintf("| %d | %d | %d |\n", $s, $inserted[$s] ?? 0, $enriched[$s] ?? 0);
        }
        $out .= sprintf("| **Total** | **%d** | **%d** |\n\n", array_sum($inserted), array_sum($enriched));

        $out .= "## Unresolved sponsors (" . count($unresolved) . ")\n\n";
        if ($unresolved === []) {
            $out .= "_None — every sponsor string resolved._\n\n";
        } else {
            $out .= "| Proposal | Name | Sponsor (raw) | Reason |\n|---|---|---|---|\n";
            foreach ($unresolved as $u) {
                $out .= sprintf(
                    "| %s | %s | `%s` | %s |\n",
                    $u['issueNum'],
                    $this->cell($u['issueName']),
                    $this->cell($u['raw']),
                    $this->cell($u['reason'] ?? '')
                );
            }
            $out .= "\n";
        }

        $out .= "## Field conflicts (" . count($conflicts) . ")\n\n";
        $out .= "_Page content disagrees with a non-empty existing value; nothing was overwritten._\n\n";
        if ($conflicts === []) {
            $out .= "_None._\n";
        } else {
            $out .= "| Proposal | Field | Existing | Page |\n|---|---|---|---|\n";
            foreach ($conflicts as $c) {
                $out .= sprintf(
                    "| %s | %s | %s | %s |\n",
                    $c['issueNum'],
                    $c['field'],
                    $this->cell($c['existing']),
                    $this->cell($c['page'])
                );
            }
        }

        return $out;
    }

    private function seasonFromFilename(string $file): ?int
    {
        return preg_match('/proposals(\d{4})/', basename($file), $m) ? (int) $m[1] : null;
    }

    /** Prefer the year embedded in the IssueNum ("2004.2a" -> 2004). */
    private function proposalSeason(ParsedProposal $p, ?int $fileSeason): int
    {
        if (preg_match('/^(\d{4})\./', $p->issueNum, $m)) {
            return (int) $m[1];
        }

        return $fileSeason ?? $p->season;
    }

    private function q(string $value): string
    {
        return $this->conn->quote($value);
    }

    private function qNull(?string $value): string
    {
        return $value === null ? 'NULL' : $this->conn->quote($value);
    }

    private function normalise(string $s): string
    {
        return preg_replace('/\s+/', ' ', trim($s)) ?? $s;
    }

    private function truncate(string $s, int $len = 120): string
    {
        $s = $this->normalise($s);

        return strlen($s) > $len ? substr($s, 0, $len) . '…' : $s;
    }

    private function cell(string $s): string
    {
        return str_replace(['|', "\n"], ['\\|', ' '], $this->truncate($s, 80));
    }
}
