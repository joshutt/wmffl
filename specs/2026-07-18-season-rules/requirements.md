# Season Rules foundation: Requirements

## Goal

A per-season league-rules architecture (branch `season-rules`) as the
foundation for Phases 11–12. League rules vary by season — scoring
rules, protection points, finance distribution, schedule structure —
but were hardcoded as season-invariant constants, duplicated across
legacy and Symfony code. Critically, **historical rule changes are not
all represented in the codebase**: Josh must recreate them from league
records, so the system needs a database-backed, season-versioned rule
store with an admin UI for entering and correcting historical values.

## Decisions

1. **Full wiring in this phase** (confirmed by Josh): not just the
   entity + admin UI — ScoreCalculatorService, TeamMoneyService, the
   `week<=14` hardcodes and the roster limit all consume the new data.
2. **Protections editing included** (confirmed by Josh): the season
   edit page also edits that season's per-team `transpoints` budgets
   and the effective `positioncost` rows.
3. **Storage shape — hybrid.** `seasons` table: typed columns for
   structure (regular_season_weeks, total_weeks, max_active_players,
   num_of_games) and finance (entry fee, three fines, six payout
   percentages); a `scoring_rules` JSON map for the ~48 scoring
   parameters (tier tables included, no migration per rediscovered
   parameter); `scoring_strategy` discriminator as the seam for
   formula-*shape* changes across eras (only `standard` ships); a JSON
   value of null = "category not awarded that season".
4. **`ScoringRuleRegistry` is the single source of truth** for every
   scoring parameter (key, label, group, type, current default). It
   drives DTO hydration, admin form generation, and the scorer's line
   labels, so math, UI and box-score explanations cannot drift.
5. **Seed 1992–2026 with current rules** plus the one delta the
   codebase records: FG60 was ×10 through 2023 (`scoring2023.php` vs
   `scoring2024.php`), ×7 from 2024. When FG60=10 *started* is
   unknown — it is seeded all the way back as the best guess; Josh
   corrects older seasons in the UI. `verified=1` only for 2024–2026.
6. **Missing season row never 500s**: SeasonRuleService synthesizes
   in-memory defaults (logged warning, no auto-insert).
7. **`Year` entity (`years` table) ignored** — zero usages, later
   cleanup candidate. New table is `seasons`.
8. **Out of scope**: Phase 12 boxscore port; Phase 11 history pages;
   legacy `football/base/scoring*.php` + `currentscore.php` (only
   score the current week under current rules; die in Phase 12);
   `HistoryController` FIRST/LAST_SEASON + MVP map (Phase 11);
   `config.protections.deadline` (operational state, not a rule).

## Hardcodes replaced

- Scoring constants: legacy `scoring.php` (twice — math + scoreString)
  and `ScoreCalculatorService` (Symfony recalc applied 2024 rules to
  every season).
- `TeamMoneyService` l.19-30: ENTRY_FEE=75, NUM_OF_GAMES=84, three
  fines, six payout percentages.
- `week<=14` in StandingsRepository (×2), StatsRepository,
  LuckService (×2), AdminMvpController, TeamMoneyService::getWins.
- `RosterMoveService::MAX_ACTIVE_PLAYERS=25` / `TOTAL_ROSTER=26`.

## Admin UI

`/admin/seasons` (commissioner-only): list all seasons (fee, weeks,
strategy, verified badge, notes excerpt); clone button creates
latest+1 as a copy (including per-team transpoints budgets, spent
points zeroed); per-season edit form with fieldsets for Structure,
Finances, Scoring (generated from the registry; blank input = not
awarded), Protections (per-team TotalPts grid with spent points
read-only; effective positioncost rows with editable cost and a
start-a-new-cost flow that closes the prior range at season−1), Notes
and Verified. The score-reprocess page warns that recalculating a past
week overwrites stored scores under that season's configured rules.
