# Season Rules foundation: Plan (as built)

Branch `season-rules`, 5 commits.

## 1. Entity, migration, registry, DTOs
- `symfony-app/src/Entity/Season.php` — table `seasons`, `season` year
  PK (matches every other table's season column), typed
  structure/finance columns with DB defaults, `scoring_strategy`,
  `scoring_rules` JSON (longtext), `verified`, `notes`.
- `symfony-app/src/Repository/SeasonRepository.php` — find /
  findAllDesc / findLatest / save.
- `symfony-app/src/Service/ScoringRuleRegistry.php` — every scoring
  parameter: key, label (legacy scoreString wording), group, type
  (`int` | `tiers` | `map`), current default. `defaults()`, `groups()`.
- `symfony-app/src/Model/{ScoringRules,FinanceRules,ScoreLine}.php` —
  ScoringRules merges stored overrides over registry defaults
  (unknown keys dropped, null = not awarded); FinanceRules readonly
  props defaulting to the pre-refactor constants; ScoreLine
  label/points/count.
- `symfony-app/migrations/Version20260718000000.php` — creates
  `seasons`, seeds 1992–2026 with a frozen snapshot of the defaults
  (deliberately not read from the registry), `k_fg60=10` for ≤2023,
  verified for 2024–2026.

## 2. SeasonRuleService
`symfony-app/src/Service/SeasonRuleService.php` — per-request cache;
`getSeason` / `getScoringRules` / `getFinanceRules` /
`getRegularSeasonWeeks` / `getMaxActivePlayers`; missing row →
in-memory default Season + logged warning.

## 3. PlayerScorerService + ScoreCalculatorService
- `symfony-app/src/Service/PlayerScorerService.php` — the one place
  scoring math lives, ported from legacy `scoring.php` (authority),
  parameterized by ScoringRules, emits `ScoreLine[]` whose sum is the
  total (Phase 12 renders these as the box-score breakdown). Line
  visibility mirrors legacy `scoreString`.
- **Bug found and fixed**: the old `ScoreCalculatorService::scoreOL`
  used strict `===` int comparisons against DBAL's float `sacks`
  values — every OL sack tier scored 0 instead of 5/2/1/… on
  recalculation. The new scorer keeps legacy's loose-match semantics
  (fractional sacks match no tier; overflow starts a full sack past
  the last tier).
- `ScoreCalculatorService` keeps its signature, loads the season's
  rules once and delegates; private scorers deleted; illegal/no-game
  activation penalty uses `illegal_lineup_penalty`.
- Golden tests (`tests/Service/PlayerScorerServiceTest.php`) written
  from the legacy formulas before deleting the old code; plus
  2023-rules FG60 test and a lines-sum-equals-total invariant.

## 4. Finance + week-bound sweep
- `TeamMoneyService`: constants deleted; `computeLedger(..., FinanceRules
  $rules = new FinanceRules())` stays pure (tests unchanged ⇒
  behavior-neutral); `getLedger` passes the season's rules;
  `getWins(season, regularSeasonWeeks)`.
- `week<=14` → `:regWeeks` parameter in `StandingsRepository`
  (getCurrentStandings/getTeamGames, default 14), `StatsRepository::
  getLeaders`, `LuckService` (injects SeasonRuleService),
  `AdminMvpController`; callers fetch the bound from SeasonRuleService
  (StandingsController, HistoryStandingsController, HomeController,
  StatsController).
- `RosterMoveService`: consts replaced by `getMaxActivePlayers($season)`
  and `getTotalRoster($season)` (= max + 1 IR slot);
  RosterMoveController display uses them.

## 5. Admin UI
`src/Controller/Admin/AdminSeasonController.php` (`/admin/seasons`,
CSRF id `admin_seasons`, manual forms per repo convention),
`templates/admin/seasons/{index,edit}.html.twig`, sidebar link in
`templates/admin/base.html.twig`. Clone copies the Season row (PHP
clone + new PK) and inserts next-season transpoints rows
(TotalPts carried, spent zeroed, existing rows skipped).
`admin_seasons_addcost` closes the effective positioncost row at
season−1 and opens an open-ended row at season.

## 6. Recalc warning
`templates/admin/scores/index.html.twig` — banner explaining that
reprocessing a past week overwrites stored scores under that season's
configured rules, linking to Season Rules.

## Known limitation (pre-existing, unchanged)
`ScoreCalculatorService`'s lineup query joins `nflrosters` with
`dateoff IS NULL` (current NFL rosters), so recalculating an old week
mis-penalizes players who have since changed NFL teams. The reprocess
tool was built for the current week; Phase 12's box-score work should
revisit as-of-week roster resolution if historical recalculation is
ever needed.
