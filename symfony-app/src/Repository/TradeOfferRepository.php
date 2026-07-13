<?php

namespace App\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Data access for the trade workflow, ported from
 * football/transactions/trades/loadTrades.inc.php with bound parameters.
 *
 * Offer shape returned by findOffer()/findPendingOffersForTeam():
 *   offerId, teamAId/teamAName, teamBId/teamBName, status (read-time
 *   expiry applied), date, expires, lastOfferTeamId (the TEAM that made
 *   the most recent offer — legacy LastOfferID quirk), prevOfferId, and
 *   terms keyed by the giving team's id:
 *     terms[teamFromId] = [players => [...], picks => [...], points => [...]]
 */
class TradeOfferRepository
{
    /** Pending offers self-destruct after this many days (legacy rule). */
    public const EXPIRY_DAYS = 7;

    public function __construct(
        private Connection $connection
    ) {
    }

    // ---- offers ----

    /**
     * All Pending offers involving the team, terms included. Offers past
     * the 7-day window come back with status 'Expired' (read-time expiry;
     * the nightly SQL job remains as belt-and-braces).
     */
    public function findPendingOffersForTeam(int $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT o.OfferID FROM offer o
             WHERE o.Status = 'Pending' AND :teamId IN (o.TeamAID, o.TeamBID)
             ORDER BY o.Date DESC, o.OfferID DESC",
            ['teamId' => $teamId]
        );

        $offers = [];
        foreach ($rows as $row) {
            $offers[] = $this->findOffer((int) $row['OfferID']);
        }

        return $offers;
    }

    public function findOffer(int $offerId): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT o.OfferID, o.TeamAID, o.TeamBID, o.Status, o.Date,
                    o.LastOfferID, o.PrevOfferID,
                    ta.name AS teamAName, tb.name AS teamBName
             FROM offer o
             JOIN team ta ON ta.teamid = o.TeamAID
             JOIN team tb ON tb.teamid = o.TeamBID
             WHERE o.OfferID = :offerId',
            ['offerId' => $offerId]
        );
        if (!$row) {
            return null;
        }

        $date = new \DateTimeImmutable($row['Date']);
        $expires = $date->modify('+' . self::EXPIRY_DAYS . ' days');
        $status = $row['Status'];
        if ($status === 'Pending' && $expires <= new \DateTimeImmutable()) {
            $status = 'Expired';
        }

        return [
            'offerId' => (int) $row['OfferID'],
            'teamAId' => (int) $row['TeamAID'],
            'teamAName' => $row['teamAName'],
            'teamBId' => (int) $row['TeamBID'],
            'teamBName' => $row['teamBName'],
            'status' => $status,
            'date' => $date,
            'expires' => $expires,
            'lastOfferTeamId' => (int) $row['LastOfferID'],
            'prevOfferId' => $row['PrevOfferID'] !== null ? (int) $row['PrevOfferID'] : null,
            'terms' => $this->getTerms($offerId, (int) $row['TeamAID'], (int) $row['TeamBID']),
        ];
    }

    /**
     * Whether it is $teamId's turn to act on the offer (the other side
     * made the most recent offer, so this side may Accept/Reject/Counter).
     */
    public function isTeamsMove(array $offer, int $teamId): bool
    {
        return $offer['lastOfferTeamId'] !== $teamId;
    }

    /**
     * @return array<int, array{players: array, picks: array, points: array}>
     *         keyed by the giving team's id (both sides always present)
     */
    private function getTerms(int $offerId, int $teamAId, int $teamBId): array
    {
        $terms = [
            $teamAId => ['players' => [], 'picks' => [], 'points' => []],
            $teamBId => ['players' => [], 'picks' => [], 'points' => []],
        ];

        $players = $this->connection->fetchAllAssociative(
            "SELECT op.TeamFromID, p.playerid,
                    CONCAT(p.firstname, ' ', p.lastname) AS name, p.pos, p.team
             FROM offeredplayers op
             JOIN newplayers p ON p.playerid = op.PlayerID
             WHERE op.OfferID = :offerId
             ORDER BY p.pos, p.lastname",
            ['offerId' => $offerId]
        );
        foreach ($players as $player) {
            $terms[(int) $player['TeamFromID']]['players'][] = [
                'playerid' => (int) $player['playerid'],
                'name' => $player['name'],
                'pos' => $player['pos'],
                'nflteam' => $player['team'],
            ];
        }

        // A NULL OrgTeam means the pick is the giving team's own
        // (legacy loadTradedPicks fallback)
        $picks = $this->connection->fetchAllAssociative(
            'SELECT op.TeamFromID, op.Season, op.Round,
                    COALESCE(op.OrgTeam, op.TeamFromID) AS orgTeamId, t.name AS orgTeamName
             FROM offeredpicks op
             JOIN team t ON t.teamid = COALESCE(op.OrgTeam, op.TeamFromID)
             WHERE op.OfferID = :offerId
             ORDER BY op.Season, op.Round',
            ['offerId' => $offerId]
        );
        foreach ($picks as $pick) {
            $terms[(int) $pick['TeamFromID']]['picks'][] = [
                'season' => (int) $pick['Season'],
                'round' => (int) $pick['Round'],
                'orgTeamId' => (int) $pick['orgTeamId'],
                'orgTeamName' => $pick['orgTeamName'],
            ];
        }

        $points = $this->connection->fetchAllAssociative(
            'SELECT op.TeamFromID, op.Season, op.Points
             FROM offeredpoints op
             WHERE op.OfferID = :offerId
             ORDER BY op.Season',
            ['offerId' => $offerId]
        );
        foreach ($points as $point) {
            $terms[(int) $point['TeamFromID']]['points'][] = [
                'season' => (int) $point['Season'],
                'points' => (int) $point['Points'],
            ];
        }

        return $terms;
    }

    // ---- comment history ----

    /**
     * The offer's stored comments, walked back across its PrevOfferID
     * amendment chain, oldest first.
     *
     * @return array<int, array{teamId: int, teamName: string, action: string, date: \DateTimeImmutable, comment: string}>
     */
    public function getCommentHistory(int $offerId): array
    {
        $chain = [];
        $current = $offerId;
        // The chain is short (one link per amendment); guard against a
        // cycle from hand-edited data
        while ($current !== null && !in_array($current, $chain, true)) {
            $chain[] = $current;
            $current = $this->connection->fetchOne(
                'SELECT PrevOfferID FROM offer WHERE OfferID = :id',
                ['id' => $current]
            );
            $current = $current !== null && $current !== false ? (int) $current : null;
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT c.TeamID, t.name AS teamName, c.Action, c.Date, c.Comment
             FROM offercomments c
             JOIN team t ON t.teamid = c.TeamID
             WHERE c.OfferID IN (:ids)
             ORDER BY c.Date, c.CommentID',
            ['ids' => $chain],
            ['ids' => ArrayParameterType::INTEGER]
        );

        return array_map(static fn (array $row) => [
            'teamId' => (int) $row['TeamID'],
            'teamName' => $row['teamName'],
            'action' => $row['Action'],
            'date' => new \DateTimeImmutable($row['Date']),
            'comment' => $row['Comment'],
        ], $rows);
    }

    // ---- builder data ----

    /**
     * Active teams for the "Offer New Trade" dropdown.
     *
     * @return array<int, array{teamid: int, name: string}>
     */
    public function getActiveTeams(): array
    {
        return array_map(static fn (array $row) => [
            'teamid' => (int) $row['teamid'],
            'name' => $row['name'],
        ], $this->connection->fetchAllAssociative(
            'SELECT teamid, name FROM team WHERE active = 1 ORDER BY name'
        ));
    }

    public function getTeamName(int $teamId): ?string
    {
        $name = $this->connection->fetchOne(
            'SELECT name FROM team WHERE teamid = :teamId',
            ['teamId' => $teamId]
        );

        return $name === false ? null : $name;
    }

    /**
     * The team's tradeable roster: current players, head coaches excluded.
     *
     * @return array<int, array{playerid: int, name: string, pos: string, nflteam: string}>
     */
    public function getTradeableRoster(int $teamId): array
    {
        return array_map(static fn (array $row) => [
            'playerid' => (int) $row['playerid'],
            'name' => $row['name'],
            'pos' => $row['pos'],
            'nflteam' => $row['team'],
        ], $this->connection->fetchAllAssociative(
            "SELECT p.playerid, CONCAT(p.firstname, ' ', p.lastname) AS name, p.pos, p.team
             FROM newplayers p
             JOIN roster r ON r.playerid = p.playerid AND r.dateoff IS NULL
             WHERE r.teamid = :teamId AND p.pos <> 'HC'
             ORDER BY p.pos, p.lastname",
            ['teamId' => $teamId]
        ));
    }

    /**
     * Future draft picks the team actually owns (not yet used on a
     * player), so a traded pick always has a known original owner.
     *
     * @return array<int, array{id: int, season: int, round: int, orgTeamId: int, orgTeamName: string}>
     */
    public function getOwnedFuturePicks(int $teamId, int $minSeason): array
    {
        return array_map(static fn (array $row) => [
            'id' => (int) $row['id'],
            'season' => (int) $row['season'],
            'round' => (int) $row['round'],
            'orgTeamId' => (int) $row['orgTeamId'],
            'orgTeamName' => $row['orgTeamName'],
        ], $this->connection->fetchAllAssociative(
            'SELECT d.id, d.Season AS season, d.Round AS round,
                    COALESCE(d.orgTeam, d.teamid) AS orgTeamId, t.name AS orgTeamName
             FROM draftpicks d
             JOIN team t ON t.teamid = COALESCE(d.orgTeam, d.teamid)
             WHERE d.teamid = :teamId AND d.Season >= :minSeason AND d.playerid IS NULL
             ORDER BY d.Season, d.Round',
            ['teamId' => $teamId, 'minSeason' => $minSeason]
        ));
    }

    /**
     * Remaining transaction-point balance per season,
     * seasons $fromSeason..$toSeason (missing seasons -> 0 remaining).
     *
     * @return array<int, int> season => points remaining
     */
    public function getPointsBalances(int $teamId, int $fromSeason, int $toSeason): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT season, TotalPts - ProtectionPts - TransPts AS remaining
             FROM transpoints
             WHERE teamid = :teamId AND season BETWEEN :fromSeason AND :toSeason',
            ['teamId' => $teamId, 'fromSeason' => $fromSeason, 'toSeason' => $toSeason]
        );

        $balances = [];
        for ($season = $fromSeason; $season <= $toSeason; $season++) {
            $balances[$season] = 0;
        }
        foreach ($rows as $row) {
            $balances[(int) $row['season']] = (int) $row['remaining'];
        }

        return $balances;
    }

    // ---- writes ----

    /**
     * Persist a new offer in one transaction: the offer row (the acting
     * team is always TeamA and LastOfferID on the new row, as legacy
     * saveOffer did), its term rows (OrgTeam always recorded on picks),
     * and the comment row when non-empty. An amend/counter first marks
     * the predecessor Modified and links the chain via PrevOfferID.
     *
     * @param array<int, array{players: array, picks: array, points: array}> $terms
     *        validated terms keyed by giving team id (TradeValidationService shape)
     * @return int the new offer's id
     */
    public function saveOffer(
        int $actingTeamId,
        int $otherTeamId,
        array $terms,
        ?int $prevOfferId,
        string $comment,
        string $commentAction
    ): int {
        return $this->connection->transactional(function (Connection $conn) use (
            $actingTeamId, $otherTeamId, $terms, $prevOfferId, $comment, $commentAction
        ) {
            if ($prevOfferId !== null) {
                $conn->executeStatement(
                    "UPDATE offer SET Status = 'Modified' WHERE OfferID = :prevOfferId",
                    ['prevOfferId' => $prevOfferId]
                );
            }

            $conn->executeStatement(
                "INSERT INTO offer (TeamAID, TeamBID, Status, Date, LastOfferID, PrevOfferID)
                 VALUES (:actingTeamId, :otherTeamId, 'Pending', now(), :actingTeamId, :prevOfferId)",
                ['actingTeamId' => $actingTeamId, 'otherTeamId' => $otherTeamId, 'prevOfferId' => $prevOfferId]
            );
            $offerId = (int) $conn->lastInsertId();

            foreach ($terms as $teamFromId => $side) {
                foreach ($side['players'] as $player) {
                    $conn->executeStatement(
                        'INSERT INTO offeredplayers (OfferID, TeamFromID, PlayerID)
                         VALUES (:offerId, :teamFromId, :playerId)',
                        ['offerId' => $offerId, 'teamFromId' => $teamFromId, 'playerId' => $player['playerid']]
                    );
                }
                foreach ($side['picks'] as $pick) {
                    $conn->executeStatement(
                        'INSERT INTO offeredpicks (OfferID, TeamFromID, Season, Round, OrgTeam)
                         VALUES (:offerId, :teamFromId, :season, :round, :orgTeam)',
                        [
                            'offerId' => $offerId, 'teamFromId' => $teamFromId,
                            'season' => $pick['season'], 'round' => $pick['round'],
                            'orgTeam' => $pick['orgTeamId'],
                        ]
                    );
                }
                foreach ($side['points'] as $point) {
                    $conn->executeStatement(
                        'INSERT INTO offeredpoints (OfferID, TeamFromID, Season, Points)
                         VALUES (:offerId, :teamFromId, :season, :points)',
                        [
                            'offerId' => $offerId, 'teamFromId' => $teamFromId,
                            'season' => $point['season'], 'points' => $point['points'],
                        ]
                    );
                }
            }

            $this->insertComment($conn, $offerId, $actingTeamId, $commentAction, $comment);

            return $offerId;
        });
    }

    /** Store a non-empty trade comment (no-op on blank input). */
    public function addComment(int $offerId, int $teamId, string $action, string $comment): void
    {
        $this->insertComment($this->connection, $offerId, $teamId, $action, $comment);
    }

    private function insertComment(Connection $conn, int $offerId, int $teamId, string $action, string $comment): void
    {
        if (trim($comment) === '') {
            return;
        }

        $conn->executeStatement(
            'INSERT INTO offercomments (OfferID, TeamID, Action, Date, Comment)
             VALUES (:offerId, :teamId, :action, now(), :comment)',
            ['offerId' => $offerId, 'teamId' => $teamId, 'action' => $action, 'comment' => trim($comment)]
        );
    }

    /**
     * Active users' email addresses for a set of teams.
     *
     * @param int[] $teamIds
     * @return array<int, array{email: string, teamId: int}>
     */
    public function getActiveUserEmails(array $teamIds): array
    {
        if ($teamIds === []) {
            return [];
        }

        return array_map(static fn (array $row) => [
            'email' => $row['Email'],
            'teamId' => (int) $row['TeamID'],
        ], $this->connection->fetchAllAssociative(
            "SELECT Email, TeamID FROM user WHERE TeamID IN (:teamIds) AND active = 'Y'",
            ['teamIds' => array_map('intval', $teamIds)],
            ['teamIds' => ArrayParameterType::INTEGER]
        ));
    }
}
