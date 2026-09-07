# Plan — 2026 Rule Changes

## 1. `Season` entity + migration: `maxIrSlots`

1. Add `maxIrSlots` to `symfony-app/src/Entity/Season.php`:
   `#[ORM\Column(name: 'max_ir_slots', type: 'integer', options: ['default' => 0])]`,
   `private int $maxIrSlots = 0;` plus getter/setter, following the exact
   style of the adjacent `maxActivePlayers` column.
2. Generate a migration (`symfony-app/bin/console make:migration` or a hand
   written `VersionYYYYMMDDHHMMSS.php` alongside the existing ones):
   - `ALTER TABLE seasons ADD max_ir_slots INT NOT NULL DEFAULT 0`.
   - Explicit per-era `UPDATE` statements:
     - `UPDATE seasons SET max_ir_slots = 999 WHERE season = 2026`
     - `UPDATE seasons SET max_ir_slots = 1 WHERE season IN (2024, 2025)`
     - `UPDATE seasons SET max_ir_slots = 3 WHERE season IN (2022, 2023)`
     - `UPDATE seasons SET max_ir_slots = 99 WHERE season IN (2020, 2021)`
   - 2019 and earlier are left at the column default of `0` — no statement
     needed for them.
   - Doc comment explaining the 2026 special-case and the per-era
     historical values (see `requirements.md` decisions), matching the
     explanatory style of `Version20260718000000.php`.

## 2. Admin Season UI

3. `symfony-app/templates/admin/seasons/edit.html.twig`: add a
   `maxIrSlots` number input next to `maxActivePlayers` in the Structure
   fieldset (same markup pattern/label style).
4. `AdminSeasonController::save()`: read and persist `maxIrSlots` the same
   way as `maxActivePlayers` (`max(0, $post->getInt('maxIrSlots', 0))` —
   floor at 0, not 1, since 0 is a valid/expected value here unlike the
   active-player count).
5. Confirm `AdminSeasonController::clone()` needs no change — `clone
   $latest` already copies every scalar column, so a newly created season
   inherits its predecessor's `maxIrSlots` automatically.

## 3. IR roster-cap logic

6. `RosterMoveService`:
   - Change `getTotalRoster(int $season): int` to
     `getMaxActivePlayers($season) + $this->seasonRules->getSeason($season)->getMaxIrSlots()`
     (or add a small `SeasonRuleService::getMaxIrSlots(int $season): int`
     accessor mirroring `getMaxActivePlayers()`, and use that — prefer this
     for symmetry with the rest of the service).
   - No other change needed in `executeMoves()` — it already calls
     `getTotalRoster($season)` and compares `resultingTotal` against it, so
     the behavior change is entirely sourced from the new dynamic value.
7. `RosterMoveController::index()`: no code change expected —
   `getTotalRoster($context['season'])` already flows through to
   `availableSlots`; verify manually that a large cap doesn't produce a
   confusing negative-becomes-huge `min()` result (it won't, `getTotalRoster`
   only grows).
8. `transactions/confirm.html.twig`: verify the existing copy ("That leaves
   you with {{ availableSlots }} available slots") still reads sensibly
   when `availableSlots` is large; no wording change anticipated, but
   look at it after the 2026 migration value is in dev data.

## 4. Scoring: blocked kicks for offense

9. `ScoringRuleRegistry.php`: add `'off_block' => ['group' => 'off', 'type'
   => 'int', 'default' => null, 'label' => 'blocked kicks']` to the RB/WR
   (also TE base) section.
10. `PlayerScorerService::scoreOffense()`: add, mirroring
    `scoreDefense()`'s block line —
    ```php
    $blocks = (int) ($this->num($row, 'blockpunt') + $this->num($row, 'blockxp') + $this->num($row, 'blockfg'));
    if ($blocks > 0 && $rules->awards('off_block')) {
        $lines[] = new ScoreLine('blocked kicks', $blocks * $rules->int('off_block'), $blocks);
    }
    ```
    Do **not** add this to `scoreHC`, `scoreQB`, `scoreK`, or `scoreOL`.
11. `football/base/scoring.php::scoreOffense()`: add the same
    `+= ($scoreArray['blockpunt'] + $scoreArray['blockxp'] +
    $scoreArray['blockfg']) * 3` line as the existing one in
    `scoreDefense()`. No season-awareness needed here (see
    `requirements.md`) — this file only ever reflects the current live
    rule. Leave `scoreHC`, `scoreQB`, `scoreK`, `scoreOL` untouched.
12. Leave `scoreDefense()` (both engines) and `def_block` untouched — this
    change is additive to offense, not a rename/move of the existing
    defense rule.

## 5. Scoring: kicker FG values

13. `football/base/scoring.php::scoreK()`: change the four literals —
    `FG30*3` → `FG30*2`, `FG40*4` → `FG40*2`, `FG50*5` → `FG50*4`,
    `FG60*7` → `FG60*6`.
14. No Symfony code change — `k_fg30`/`k_fg40`/`k_fg50`/`k_fg60` are
    already season-configurable; Josh sets the new values for 2026 via
    `/admin/seasons` after this branch merges (see `validation.md` — not a
    blocker for merge).

## 6. Tests

15. `RosterMoveServiceTest`: replace the existing "including IR" assertion
    with cases against explicit `maxIrSlots` values — one small value that
    still trips the over-limit error, one large value (or the real 2026
    row) that permits many IR players.
16. `PlayerScorerServiceTest`: add cases for `scoreOffense()`/RB or WR with
    a nonzero `blockpunt`/`blockxp`/`blockfg` scoring `off_block` points
    when the rule is awarded, and 0 when it's `null` (default/historical).
    Add/confirm a case that HC/QB/K/OL scoring is unaffected by nonzero
    block stats (regression-proofs the exclusion).
17. Legacy `test/ScoreTest.php` (or wherever `scoring.php` functions are
    covered): add a `scoreOffense()` blocked-kick case, and update/add a
    `scoreK()` case for the new FG point values.
18. Full suite green: `vendor/bin/phpunit test/` (root) and
    `symfony-app/vendor/bin/phpunit tests/` (Symfony), using
    `--coverage-clover coverage.xml` if coverage is requested (never
    `--coverage-text`, per project convention).
