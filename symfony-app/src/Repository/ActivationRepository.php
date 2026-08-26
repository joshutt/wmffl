<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Reads behind the activations pages, ported from
 * football/activate/{submitactivations,currentactivations}.php with
 * bound parameters in place of the legacy string interpolation.
 *
 * Kickoff times come back two ways: `kickoff` for display and
 * `kickoffTs` (UNIX_TIMESTAMP, computed by the database) for the lock
 * arithmetic. The kickoff column is stored in the database server's
 * local time while PHP runs in UTC, so letting MySQL produce the epoch
 * keeps the two from disagreeing — the legacy pages papered over this
 * with putenv('TZ=US/Eastern') and CONVERT_TZ() in different places.
 */
class ActivationRepository
{
    /**
     * Injury statuses shortened for the lineup tables, per legacy
     * football/utils/injuryUtils.php shortenInjury(). Unknown statuses
     * render as blank; a current IR stint overrides everything.
     */
    private const INJURY_SHORT = [
        'P' => 'Prob', 'Probable' => 'Prob',
        'Q' => 'Ques', 'Questionable' => 'Ques',
        'D' => 'Doub', 'Doubtful' => 'Doub',
        'O' => 'Out', 'Out' => 'Out',
        'I' => 'NFL IR', 'IR' => 'NFL IR', 'IR-NFI' => 'NFL IR', 'IR-PUP' => 'NFL IR',
        'S' => 'Susp', 'Suspended' => 'Susp',
        'Covid' => 'Covid', 'COVID-IR' => 'Covid', 'Holdout' => 'Covid',
    ];

    /**
     * The week whose activation deadline closes the season: players
     * acquired after it cannot be activated again that season. Legacy
     * hardcoded 14 in noActivateSql.
     */
    private const ROSTER_LOCK_WEEK = 14;

    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * A team's whole roster for one week, with everything the submit
     * form needs: whether the player is already activated, when his NFL
     * game kicks off, who he plays and his injury status.
     *
     * @return list<array<string, mixed>>
     */
    public function getSubmitRoster(int $season, int $week, int $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT CONCAT(p.firstname, ' ', p.lastname) AS name, p.lastname, p.pos, n.nflteamid,
                    a.playerid AS activeId, g.kickoff, UNIX_TIMESTAMP(g.kickoff) AS kickoffTs,
                    g.homeTeam, g.roadTeam, p.playerid, i.status, i.details, ir.current AS ir
             FROM players p
             JOIN roster r ON p.playerid = r.playerid AND r.dateoff IS NULL
             LEFT JOIN nflrosters n ON n.playerid = r.playerid AND n.dateoff IS NULL
             LEFT JOIN activations a ON a.season = :season AND a.week = :week
                   AND p.playerid = a.playerid AND a.teamid = r.teamid
             LEFT JOIN nflgames g ON g.season = :season AND g.week = :week
                   AND n.nflteamid IN (g.homeTeam, g.roadTeam)
             LEFT JOIN injuries i ON i.playerid = r.playerid AND i.season = g.season AND i.week = g.week
             LEFT JOIN ir ON ir.playerid = p.playerid AND ir.dateoff IS NULL
             WHERE r.teamid = :teamId
             ORDER BY p.pos, p.lastname",
            ['season' => $season, 'week' => $week, 'teamId' => $teamId]
        );

        return array_map(fn (array $row) => $this->toPlayer($row), $rows);
    }

    /**
     * Players this team acquired after the week-14 activation deadline;
     * league rules keep them on the bench for the rest of the season
     * (legacy noActivateSql).
     *
     * @return list<int>
     */
    public function getPostDeadlineAcquisitions(int $season, int $teamId): array
    {
        $ids = $this->connection->fetchFirstColumn(
            'SELECT p.playerid
             FROM players p
             JOIN roster r1 ON p.playerid = r1.playerid AND r1.dateoff IS NULL
             JOIN roster r2 ON p.playerid = r2.playerid
             JOIN weekmap w ON w.season = :season AND w.week = :lockWeek AND r2.dateoff > w.ActivationDue
             WHERE r1.teamid = :teamId AND r1.teamid <> r2.teamid',
            ['season' => $season, 'lockWeek' => self::ROSTER_LOCK_WEEK, 'teamId' => $teamId]
        );

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * Free-agent head coaches a team may borrow this week: unrostered,
     * on an NFL roster, kicking off more than 30 minutes from now and
     * not already activated by somebody else.
     *
     * The 30-minute horizon is this query's own rule (how much lead time
     * a free-agent HC pickup needs) and is deliberately not the same
     * value as the 5-minute lineup lock.
     *
     * @return list<array<string, mixed>>
     */
    public function getActingHeadCoachOptions(int $season, int $week, int $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT CONCAT(p.firstname, ' ', p.lastname) AS name, p.lastname, p.pos, n.nflteamid,
                    a.playerid AS activeId, g.kickoff, UNIX_TIMESTAMP(g.kickoff) AS kickoffTs,
                    g.homeTeam, g.roadTeam, p.playerid, NULL AS status, NULL AS details, NULL AS ir
             FROM players p
             LEFT JOIN roster r ON p.playerid = r.playerid AND r.dateoff IS NULL
             LEFT JOIN nflrosters n ON n.playerid = p.playerid AND n.dateoff IS NULL
             LEFT JOIN nflgames g ON g.season = :season AND g.week = :week
                   AND n.nflteamid IN (g.homeTeam, g.roadTeam)
             LEFT JOIN activations a ON a.season = g.season AND a.week = g.week AND p.playerid = a.playerid
             WHERE p.pos = 'HC' AND r.playerid IS NULL AND n.playerid IS NOT NULL
               AND g.kickoff > DATE_ADD(now(), INTERVAL 30 MINUTE)
               AND (a.playerid IS NULL OR a.teamid = :teamId)
             ORDER BY p.lastname",
            ['season' => $season, 'week' => $week, 'teamId' => $teamId]
        );

        return array_map(fn (array $row) => $this->toPlayer($row), $rows);
    }

    /**
     * Weeks still open for submission, for the week picker. Week 0 is the
     * off-season placeholder row weekmap carries between seasons - never
     * a week a lineup can be set for, so it never makes this list.
     *
     * @return list<array{week: int, weekname: string}>
     */
    public function getWeekOptions(int $season): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT week, weekname FROM weekmap WHERE Season = :season AND week > 0 AND EndDate > now() ORDER BY week',
            ['season' => $season]
        );

        return array_map(
            fn (array $row) => ['week' => (int) $row['week'], 'weekname' => (string) ($row['weekname'] ?? '')],
            $rows
        );
    }

    /**
     * Every team's lineup for one week, ordered so a game's two teams
     * come back together, home side (TeamA) first — legacy ordered by
     * a.teamid, which sorted NULLs (a team with no activations) ahead of
     * everything and gave no stable A/B order. Teams with no activations
     * still appear, with a null pos.
     *
     * @return list<array<string, mixed>>
     */
    public function getCurrentActivations(int $season, int $week): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT s.gameid, s.TeamA AS teamA, s.TeamB AS teamB, tn.teamid, tn.name,
                    p.pos, p.firstname, p.lastname, p.playerid,
                    r.nflteamid, g.kickoff, UNIX_TIMESTAMP(g.kickoff) AS kickoffTs,
                    g.homeTeam, g.roadTeam, i.status, i.details, ir.current AS ir
             FROM teamnames tn
             JOIN schedule s ON tn.teamid IN (s.teama, s.teamb) AND tn.season = s.season
             LEFT JOIN activations a ON a.season = s.season AND a.week = s.week
                   AND a.teamid IN (s.TeamA, s.TeamB) AND tn.teamid = a.teamid
             LEFT JOIN players p ON a.playerid = p.playerid
             JOIN weekmap wm ON s.season = wm.season AND s.week = wm.week
             LEFT JOIN injuries i ON i.playerid = p.playerid AND i.season = wm.season AND i.week = wm.week
             LEFT JOIN ir ON ir.playerid = p.playerid AND ir.dateoff IS NULL
             LEFT JOIN nflrosters r ON r.dateon <= wm.activationDue
                   AND (r.dateoff >= wm.activationDue OR r.dateoff IS NULL) AND r.playerid = p.playerid
             LEFT JOIN nflgames g ON a.season = g.season AND a.week = g.week
                   AND r.nflteamid IN (g.homeTeam, g.roadTeam)
             WHERE s.season = :season AND s.week = :week
             ORDER BY s.gameid, tn.teamid = s.TeamB, tn.teamid, p.pos, p.lastname, p.playerid",
            ['season' => $season, 'week' => $week]
        );
    }

    /**
     * The playerids a team currently rosters — exactly the set the
     * submit form offers, and therefore the set a save is allowed to
     * activate (an acting head coach is the one documented exception).
     *
     * @return list<int>
     */
    public function getRosteredPlayerIds(int $season, int $teamId): array
    {
        $ids = $this->connection->fetchFirstColumn(
            'SELECT r.playerid FROM roster r WHERE r.teamid = :teamId AND r.dateoff IS NULL',
            ['teamId' => $teamId]
        );

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /** Teams that exist in a season, for the admin override's picker. */
    public function getTeamOptions(int $season): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT tn.teamid, tn.name FROM teamnames tn WHERE tn.season = :season ORDER BY tn.name',
            ['season' => $season]
        );
    }

    /**
     * Every week a season has, open or closed, for the pickers that look
     * backwards as well as forwards (getWeekOptions only offers the ones
     * still accepting submissions). Week 0 (off-season) is excluded here
     * too - the admin override can reach back through every played week,
     * but there is never a lineup to set or view for the off-season.
     *
     * @return list<array{week: int, weekname: string}>
     */
    public function getAllWeeks(int $season): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT week, weekname FROM weekmap WHERE Season = :season AND week > 0 ORDER BY week',
            ['season' => $season]
        );

        return array_map(
            fn (array $row) => ['week' => (int) $row['week'], 'weekname' => (string) ($row['weekname'] ?? '')],
            $rows
        );
    }

    /**
     * The short injury label for a raw status ('Questionable' => 'Ques'),
     * blank when the status means nothing to us.
     */
    public static function injuryShort(string $status): string
    {
        return self::INJURY_SHORT[$status] ?? '';
    }

    /** Normalize one roster/HC row into the shape the views and service use. */
    private function toPlayer(array $row): array
    {
        $ir = (string) ($row['ir'] ?? '') === '1';
        $status = (string) ($row['status'] ?? '');
        $label = self::injuryShort($status);

        return [
            'playerid' => (int) $row['playerid'],
            'name' => (string) $row['name'],
            // The surname the roster query sorted on. The browser needs
            // it to keep the lists in the server's order after a drag -
            // the label reads "First Last", so sorting on what is shown
            // would not agree with what the server sent.
            'lastname' => (string) ($row['lastname'] ?? ''),
            'pos' => (string) ($row['pos'] ?? ''),
            'nfl' => (string) ($row['nflteamid'] ?? ''),
            'opp' => self::opponent($row),
            'kickoff' => $row['kickoff'] ?? null,
            'kickoffTs' => isset($row['kickoffTs']) ? (int) $row['kickoffTs'] : null,
            'active' => ($row['activeId'] ?? null) !== null,
            'injuryLabel' => $ir ? 'IR' : $label,
            'injuryDetail' => $label === '' ? '' : $status . ': ' . (string) ($row['details'] ?? ''),
            'ir' => $ir,
        ];
    }

    /**
     * "vs BUF" / "@ BUF" / "Bye" / "" — legacy getPlayerOpp(): a player
     * with no NFL team gets nothing at all, one with a team but no game
     * gets a bye.
     */
    public static function opponent(array $row): string
    {
        $nfl = (string) ($row['nflteamid'] ?? '');
        if ($nfl === '') {
            return '';
        }
        if (($row['kickoff'] ?? null) === null) {
            return 'Bye';
        }
        if ($nfl === (string) $row['homeTeam']) {
            return 'vs ' . $row['roadTeam'];
        }
        if ($nfl === (string) $row['roadTeam']) {
            return '@ ' . $row['homeTeam'];
        }

        return '';
    }
}
