<?php

namespace App\Service;

use App\Model\LineupRules;
use App\Repository\ActivationRepository;
use Doctrine\DBAL\Connection;

/**
 * Lineup submission: who is locked, what the submit form shows, and the
 * one write path into `activations`.
 *
 * Replaces football/activate/{submitactivations,processActivations}.php.
 * The legacy save built its INSERT by interpolating raw $_REQUEST values
 * and never checked that the submitted players were on the team; here
 * every id is validated as an integer, checked against the roster and
 * its position, and bound as a parameter.
 *
 * `activations` is the only table this service writes; the retired
 * side tables the legacy handler also touched are not written by
 * anything here.
 */
class ActivationService
{
    /**
     * How long before kickoff a player's activation freezes. Proposal
     * 2023.2 cut this from two hours to five minutes; the same value now
     * drives both the submit form and the current-activations markers,
     * which used to disagree (5 vs 30 minutes).
     */
    public const LOCK_LEAD_SECONDS = 5 * 60;

    public function __construct(
        private ActivationRepository $activations,
        private SeasonRuleService $seasonRules,
        private Connection $connection
    ) {
    }

    /**
     * Whether a player's slot is frozen: his NFL game is within five
     * minutes of kickoff. A player with no game this week (bye, or no
     * NFL team at all) is never locked.
     */
    public function lockStateFor(array $player, \DateTimeImmutable $now): bool
    {
        $kickoffTs = $player['kickoffTs'] ?? null;
        if ($kickoffTs === null) {
            return false;
        }

        return $now->getTimestamp() > (int) $kickoffTs - self::LOCK_LEAD_SECONDS;
    }

    /**
     * Everything the lineup form renders: the starter/reserve split with
     * locks applied, and the acting-head-coach picker when the team's
     * own HC has no game.
     *
     * $submitted, when given, is the rejected POST's selections so a
     * re-render keeps the boxes the user actually ticked rather than
     * reverting to what is stored: position => list of playerids, plus
     * the optional `actHC` (bool) and `actHCid` (int) picker state.
     *
     * $bypassLocks is the commissioner override: every player stays
     * editable no matter what has already kicked off.
     *
     * @return array<string, mixed>
     */
    public function buildSubmitView(
        int $season,
        int $week,
        int $teamId,
        ?array $submitted = null,
        ?\DateTimeImmutable $now = null,
        bool $bypassLocks = false
    ): array {
        $now ??= new \DateTimeImmutable();
        $roster = $this->activations->getSubmitRoster($season, $week, $teamId);
        $postDeadline = array_flip($this->activations->getPostDeadlineAcquisitions($season, $teamId));

        $starters = [];
        $reserves = [];
        $latestDeadline = null;
        $needsActingHc = false;

        foreach ($roster as $player) {
            $player['lock'] = $bypassLocks ? false : $this->lockStateFor($player, $now);

            if ($player['kickoffTs'] !== null) {
                $deadline = (int) $player['kickoffTs'] - self::LOCK_LEAD_SECONDS;
                $latestDeadline = max($latestDeadline ?? $deadline, $deadline);
            }

            // Acquired after the week-14 deadline: benched for the rest
            // of the season, whatever his kickoff says.
            $player['postDeadline'] = isset($postDeadline[$player['playerid']]);
            if ($player['postDeadline'] && !$bypassLocks) {
                $player['lock'] = true;
                $reserves[] = $player;
                continue;
            }

            if ($this->isStarter($player, $submitted)) {
                $starters[] = $player;
            } else {
                $reserves[] = $player;
            }

            if ($player['pos'] === 'HC' && $player['kickoffTs'] === null && $player['nfl'] !== '') {
                $needsActingHc = true;
            }
        }

        // Everything freezes once the last kickoff on the roster has
        // passed. A roster with no games at all (every player on a bye)
        // has no such moment, so nothing locks — legacy's zero-valued
        // "latest kickoff" locked that case instead.
        $allLock = !$bypassLocks && $latestDeadline !== null && $now->getTimestamp() > $latestDeadline;

        $actingHc = null;
        $options = $needsActingHc
            ? $this->activations->getActingHeadCoachOptions($season, $week, $teamId)
            : [];
        // No eligible free agent (an off-season week, or every coach's
        // game already too close) means no picker at all, rather than an
        // empty dropdown next to a checkbox that would do nothing.
        if ($options !== []) {
            $selected = null;
            foreach ($options as $option) {
                if ($option['active']) {
                    $selected = $option['playerid'];
                }
            }
            if ($submitted !== null) {
                $selected = $submitted['actHCid'] ?? $selected;
            }
            $actingHc = [
                'options' => $options,
                'selected' => $selected === null ? null : (int) $selected,
                'checked' => $submitted === null ? $selected !== null : ($submitted['actHC'] ?? false),
            ];
        }

        return [
            'season' => $season,
            'week' => $week,
            'teamId' => $teamId,
            'starters' => $starters,
            'reserves' => $reserves,
            'allLock' => $allLock,
            'actingHc' => $actingHc,
            'rules' => $this->seasonRules->getLineupRules($season),
        ];
    }

    /**
     * Store a team's lineup for one week, replacing whatever is there.
     *
     * Returns the list of problems; an empty list means the lineup was
     * saved. Nothing is written unless every check passes.
     *
     * @param array<string, list<mixed>> $selectionsByPos raw position => ids, straight off the request
     * @param bool $allowIllegal commissioner override: skip the position-count rules
     * @param bool $bypassLocks commissioner override: ignore kickoff locks
     * @return list<string>
     */
    public function save(
        int $season,
        int $week,
        int $teamId,
        array $selectionsByPos,
        ?int $actingHcId = null,
        bool $allowIllegal = false,
        bool $bypassLocks = false,
        ?\DateTimeImmutable $now = null
    ): array {
        $now ??= new \DateTimeImmutable();
        $rules = $this->seasonRules->getLineupRules($season);

        $selections = [];
        $errors = [];
        $seen = [];
        foreach ($rules->positions() as $pos) {
            $selections[$pos] = [];
            foreach ((array) ($selectionsByPos[$pos] ?? []) as $raw) {
                $id = $this->toPlayerId($raw);
                if ($id === null) {
                    $errors[] = 'That lineup contained something that is not a player - nothing was saved.';
                    continue;
                }
                if (isset($seen[$id])) {
                    $errors[] = 'The same player cannot be activated twice.';
                    continue;
                }
                $seen[$id] = true;
                $selections[$pos][] = $id;
            }
        }

        if ($actingHcId !== null && $actingHcId <= 0) {
            $errors[] = 'That acting head coach is not a player - nothing was saved.';
            $actingHcId = null;
        }

        if ($errors !== []) {
            return array_values(array_unique($errors));
        }

        // Counts, including the borrowed head coach: he fills the same
        // slot the team's own HC would have.
        $counts = array_map('count', $selections);
        if ($actingHcId !== null) {
            $counts['HC'] = ($counts['HC'] ?? 0) + 1;
        }
        if (!$allowIllegal) {
            $errors = array_merge($errors, $rules->validate($counts));
        }

        $errors = array_merge($errors, $this->checkOwnershipAndLocks(
            $season, $week, $teamId, $selections, $actingHcId, $bypassLocks, $now
        ));

        if ($errors !== []) {
            return array_values(array_unique($errors));
        }

        $this->replaceActivations($season, $week, $teamId, $selections, $actingHcId);

        return [];
    }

    /**
     * Every submitted player must be one this team could actually have
     * ticked: on its roster, at the position he was submitted under, and
     * not frozen by a kickoff that has already come and gone.
     *
     * @return list<string>
     */
    private function checkOwnershipAndLocks(
        int $season,
        int $week,
        int $teamId,
        array $selections,
        ?int $actingHcId,
        bool $bypassLocks,
        \DateTimeImmutable $now
    ): array {
        $errors = [];
        $roster = [];
        $latestDeadline = null;
        foreach ($this->activations->getSubmitRoster($season, $week, $teamId) as $player) {
            $roster[$player['playerid']] = $player;
            if ($player['kickoffTs'] !== null) {
                $deadline = (int) $player['kickoffTs'] - self::LOCK_LEAD_SECONDS;
                $latestDeadline = max($latestDeadline ?? $deadline, $deadline);
            }
        }
        $allLock = !$bypassLocks && $latestDeadline !== null && $now->getTimestamp() > $latestDeadline;
        $postDeadline = array_flip($this->activations->getPostDeadlineAcquisitions($season, $teamId));

        $wanted = [];
        foreach ($selections as $pos => $ids) {
            foreach ($ids as $id) {
                $wanted[$id] = $pos;

                if (!isset($roster[$id])) {
                    $errors[] = 'You can only activate players on your own roster.';
                    continue;
                }
                if ($roster[$id]['pos'] !== $pos) {
                    $errors[] = sprintf(
                        '%s is a %s and cannot be activated at %s.',
                        $roster[$id]['name'], $roster[$id]['pos'], $pos
                    );
                }
            }
        }

        if ($actingHcId !== null) {
            $eligible = array_column($this->activations->getActingHeadCoachOptions($season, $week, $teamId), 'playerid');
            if (!in_array($actingHcId, $eligible, true)) {
                $errors[] = 'That head coach is not available as an acting head coach this week.';
            }
        }

        if ($bypassLocks) {
            return $errors;
        }

        foreach ($roster as $id => $player) {
            $locked = $allLock || $this->lockStateFor($player, $now) || isset($postDeadline[$id]);
            if (!$locked) {
                continue;
            }
            $willStart = isset($wanted[$id]);
            if ($willStart !== $player['active']) {
                $errors[] = sprintf('%s is locked for this week and cannot be changed.', $player['name']);
            }
        }

        return $errors;
    }

    /**
     * Delete-then-insert in one transaction, every value bound. The
     * team's whole week is replaced, so a half-applied lineup is never
     * left behind.
     */
    private function replaceActivations(int $season, int $week, int $teamId, array $selections, ?int $actingHcId): void
    {
        $rows = [];
        foreach ($selections as $pos => $ids) {
            foreach ($ids as $id) {
                $rows[] = [$pos, $id];
            }
        }
        if ($actingHcId !== null) {
            $rows[] = ['HC', $actingHcId];
        }

        $this->connection->transactional(function (Connection $connection) use ($season, $week, $teamId, $rows) {
            $connection->executeStatement(
                'DELETE FROM activations WHERE season = :season AND week = :week AND teamid = :teamId',
                ['season' => $season, 'week' => $week, 'teamId' => $teamId]
            );

            if ($rows === []) {
                return;
            }

            $placeholders = [];
            $params = ['season' => $season, 'week' => $week, 'teamId' => $teamId];
            foreach ($rows as $i => [$pos, $id]) {
                $placeholders[] = "(:season, :week, :teamId, :pos$i, :player$i)";
                $params["pos$i"] = $pos;
                $params["player$i"] = $id;
            }

            $connection->executeStatement(
                'INSERT INTO activations (season, week, teamid, pos, playerid) VALUES '
                . implode(', ', $placeholders),
                $params
            );
        });
    }

    /**
     * A playerid is a positive integer and nothing else — "1) ; DROP
     * TABLE activations; --" casts to 1 in PHP, so a cast is not a
     * check.
     */
    private function toPlayerId(mixed $raw): ?int
    {
        if (!is_scalar($raw)) {
            return null;
        }
        $id = filter_var((string) $raw, FILTER_VALIDATE_INT);

        return $id === false || $id <= 0 ? null : $id;
    }

    /**
     * Where a player sits on the re-rendered form: after a rejected POST
     * the user's own ticks decide, otherwise what is stored does.
     */
    private function isStarter(array $player, ?array $submitted): bool
    {
        if ($submitted === null) {
            return $player['active'];
        }

        $ticked = array_map('intval', (array) ($submitted[$player['pos']] ?? []));

        return in_array($player['playerid'], $ticked, true);
    }

    /** The rules a page needs to render counters and limits. */
    public function getLineupRules(int $season): LineupRules
    {
        return $this->seasonRules->getLineupRules($season);
    }
}
