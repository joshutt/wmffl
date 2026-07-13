<?php

namespace App\Service;

use App\Enum\OfferCommentActionEnum;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Accept-time re-validation and trade execution, ported from
 * loadTrades.inc.php validateTrade()/acceptTrade().
 *
 * Differences from legacy (per the phase requirements):
 *  - roster membership is compared by player id (the legacy
 *    !array_search() object check failed on a match at index 0);
 *  - pick matching treats a NULL draftpicks.orgTeam as the owning
 *    team's own pick, and the transfer stamps orgTeam so provenance
 *    survives the move;
 *  - both sides must hold a transpoints row for every points season
 *    (legacy's blind UPDATE silently discarded the receiver's points);
 *  - the ~8 separate statements now run in ONE transaction.
 */
class TradeExecutionService
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Can the offer still be honored as stored? Empty result = yes.
     * Callers auto-reject on failure (legacy tradeinvalid.php behavior).
     *
     * @param array $offer TradeOfferRepository offer shape
     * @return string[] human-readable failure reasons
     */
    public function validateAcceptance(array $offer): array
    {
        $errors = [];

        foreach ($offer['terms'] as $givingTeamId => $side) {
            $givingTeamId = (int) $givingTeamId;
            $receivingTeamId = $this->otherTeamId($offer, $givingTeamId);
            $teamName = $this->teamName($offer, $givingTeamId);

            $playerIds = array_column($side['players'], 'playerid');
            if ($playerIds !== []) {
                $onRoster = array_map('intval', $this->connection->fetchFirstColumn(
                    'SELECT playerid FROM roster
                     WHERE teamid = :teamId AND dateoff IS NULL AND playerid IN (:playerIds)',
                    ['teamId' => $givingTeamId, 'playerIds' => $playerIds],
                    ['playerIds' => ArrayParameterType::INTEGER]
                ));
                foreach ($side['players'] as $player) {
                    if (!in_array((int) $player['playerid'], $onRoster, true)) {
                        $errors[] = "{$player['name']} is no longer on the $teamName roster.";
                    }
                }
            }

            foreach ($side['picks'] as $pick) {
                $count = (int) $this->connection->fetchOne(
                    'SELECT COUNT(*) FROM draftpicks
                     WHERE Season = :season AND Round = :round AND teamid = :teamId
                     AND COALESCE(orgTeam, teamid) = :orgTeamId AND playerid IS NULL',
                    [
                        'season' => $pick['season'], 'round' => $pick['round'],
                        'teamId' => $givingTeamId, 'orgTeamId' => $pick['orgTeamId'],
                    ]
                );
                if ($count !== 1) {
                    $errors[] = "The {$pick['season']} round {$pick['round']} pick"
                        . " ({$pick['orgTeamName']}'s) no longer belongs to the $teamName.";
                }
            }

            foreach ($side['points'] as $point) {
                $remaining = $this->connection->fetchOne(
                    'SELECT TotalPts - ProtectionPts - TransPts FROM transpoints
                     WHERE teamid = :teamId AND season = :season',
                    ['teamId' => $givingTeamId, 'season' => $point['season']]
                );
                if ($remaining === false || (int) $remaining < $point['points']) {
                    $errors[] = "The $teamName no longer have {$point['points']} points"
                        . " to give in {$point['season']}.";
                    continue;
                }
                $receiverHasRow = $this->connection->fetchOne(
                    'SELECT COUNT(*) FROM transpoints WHERE teamid = :teamId AND season = :season',
                    ['teamId' => $receivingTeamId, 'season' => $point['season']]
                );
                if ((int) $receiverHasRow !== 1) {
                    $errors[] = 'The ' . $this->teamName($offer, $receivingTeamId)
                        . " cannot receive points for {$point['season']}.";
                }
            }
        }

        return $errors;
    }

    /**
     * Execute the accepted trade in one transaction: status, roster
     * swaps, pick transfers, point adjustments, trade-history rows
     * sharing a fresh TradeGroup, the two 'Trade' transactions marker
     * rows (legacy playerid=1 quirk kept for transaction counts), and
     * the accepted comment.
     */
    public function execute(array $offer, int $acceptingTeamId, string $comment): void
    {
        $this->connection->transactional(function (Connection $conn) use ($offer, $acceptingTeamId, $comment) {
            $conn->executeStatement(
                "UPDATE offer SET Status = 'Accept' WHERE OfferID = :offerId",
                ['offerId' => $offer['offerId']]
            );

            $tradeGroup = (int) $conn->fetchOne('SELECT COALESCE(MAX(tradegroup), 0) + 1 FROM trade');

            foreach ($offer['terms'] as $givingTeamId => $side) {
                $givingTeamId = (int) $givingTeamId;
                $receivingTeamId = $this->otherTeamId($offer, $givingTeamId);

                $playerIds = array_column($side['players'], 'playerid');
                if ($playerIds !== []) {
                    $conn->executeStatement(
                        'UPDATE roster SET Dateoff = now()
                         WHERE Dateoff IS NULL AND teamid = :teamId AND playerid IN (:playerIds)',
                        ['teamId' => $givingTeamId, 'playerIds' => $playerIds],
                        ['playerIds' => ArrayParameterType::INTEGER]
                    );
                    foreach ($playerIds as $playerId) {
                        $conn->executeStatement(
                            'INSERT INTO roster (playerid, teamid, dateon) VALUES (:playerId, :teamId, now())',
                            ['playerId' => $playerId, 'teamId' => $receivingTeamId]
                        );
                        $conn->executeStatement(
                            'INSERT INTO trade (TeamFromID, TeamToID, PlayerID, Other, Date, TradeGroup)
                             VALUES (:fromId, :toId, :playerId, NULL, now(), :tradeGroup)',
                            [
                                'fromId' => $givingTeamId, 'toId' => $receivingTeamId,
                                'playerId' => $playerId, 'tradeGroup' => $tradeGroup,
                            ]
                        );
                    }
                }

                foreach ($side['picks'] as $pick) {
                    // Stamping orgTeam keeps the pick's provenance after
                    // it leaves the original owner's hands
                    $conn->executeStatement(
                        'UPDATE draftpicks SET teamid = :toId, orgTeam = :orgTeamId
                         WHERE Season = :season AND Round = :round AND teamid = :fromId
                         AND COALESCE(orgTeam, teamid) = :orgTeamId AND playerid IS NULL',
                        [
                            'toId' => $receivingTeamId, 'orgTeamId' => $pick['orgTeamId'],
                            'season' => $pick['season'], 'round' => $pick['round'],
                            'fromId' => $givingTeamId,
                        ]
                    );
                }

                foreach ($side['points'] as $point) {
                    $conn->executeStatement(
                        'UPDATE transpoints SET TotalPts = TotalPts - :points
                         WHERE teamid = :teamId AND season = :season',
                        ['points' => $point['points'], 'teamId' => $givingTeamId, 'season' => $point['season']]
                    );
                    $conn->executeStatement(
                        'UPDATE transpoints SET TotalPts = TotalPts + :points
                         WHERE teamid = :teamId AND season = :season',
                        ['points' => $point['points'], 'teamId' => $receivingTeamId, 'season' => $point['season']]
                    );
                }

                // Picks and points render in trade history as one summary
                // sentence per giving side (legacy $fromString/$toString)
                $summary = $this->sideSummary($side);
                if ($summary !== '') {
                    $conn->executeStatement(
                        'INSERT INTO trade (TeamFromID, TeamToID, PlayerID, Other, Date, TradeGroup)
                         VALUES (:fromId, :toId, NULL, :summary, now(), :tradeGroup)',
                        [
                            'fromId' => $givingTeamId, 'toId' => $receivingTeamId,
                            'summary' => $summary, 'tradeGroup' => $tradeGroup,
                        ]
                    );
                }
            }

            foreach ([$offer['teamAId'], $offer['teamBId']] as $teamId) {
                $conn->executeStatement(
                    "INSERT INTO transactions (teamid, playerid, method, date)
                     VALUES (:teamId, 1, 'Trade', now())",
                    ['teamId' => $teamId]
                );
            }

            if (trim($comment) !== '') {
                $conn->executeStatement(
                    'INSERT INTO offercomments (OfferID, TeamID, Action, Date, Comment)
                     VALUES (:offerId, :teamId, :action, now(), :comment)',
                    [
                        'offerId' => $offer['offerId'], 'teamId' => $acceptingTeamId,
                        'action' => OfferCommentActionEnum::Accepted->value, 'comment' => trim($comment),
                    ]
                );
            }
        });
    }

    /** Legacy summary wording: "a 3rd round pick in 2027 and 5 protection points in 2026 " */
    private function sideSummary(array $side): string
    {
        $parts = [];
        foreach ($side['picks'] as $pick) {
            $parts[] = 'a ' . $pick['round'] . TradeMailer::ordinalEnding($pick['round'])
                . ' round pick in ' . $pick['season'] . ' ';
        }
        foreach ($side['points'] as $point) {
            $parts[] = $point['points'] . ' protection points in ' . $point['season'] . ' ';
        }

        return implode('and ', $parts);
    }

    private function teamName(array $offer, int $teamId): string
    {
        return $teamId === $offer['teamAId'] ? $offer['teamAName'] : $offer['teamBName'];
    }

    private function otherTeamId(array $offer, int $teamId): int
    {
        return $teamId === $offer['teamAId'] ? $offer['teamBId'] : $offer['teamAId'];
    }
}
