# Season Rules foundation: Validation

All performed 2026-07-18 on the dev database.

## Automated

- `cd symfony-app && vendor/bin/phpunit tests/` — **702 tests, 2133
  assertions, OK** (672 pre-existing + 30 new: SeasonRuleService 6,
  PlayerScorerService 18 golden, AdminSeasonController 6).
- `php bin/console lint:container` and `lint:twig templates/admin/` —
  clean.

## Scoring-engine equivalence (the strong check)

Compared legacy `football/base/scoring.php` (included directly, the
authority) against `PlayerScorerService` with default rules over
**every stats row joined to a player position — 451,253 rows: zero
mismatches**. This covers all ten positions including fractional
sacks, the OL loose-switch semantics and every bonus/tier path.
(Script: run ad hoc; recreate by joining `stats` to `players` on
`flmid=statid` and comparing per-position totals.)

## Migration + seed

`doctrine:migrations:migrate` applied Version20260718000000 cleanly.
`SELECT season, JSON_EXTRACT(scoring_rules,'$.k_fg60') FROM seasons`:
10 for 1992–2023, 7 for 2024–2026; 35 rows; verified=1 only 2024–2026.

## FG60 season-correctness

Read-only recompute of 2023 week 6 (Butker FG60, team 13): team 13
scores exactly **+3 under 2023 rules (FG60=10) vs 2024 rules** —
57 vs 54. The engine picks the right rules per season.

**Finding**: full-week recomputed totals do NOT match stored scores
for past weeks — under either engine, old or new. Cause is the
pre-existing `nflrosters dateoff IS NULL` (current-roster) join in the
lineup query: players who changed NFL teams since are treated as
illegal activations. The reprocess tool remains a current-week tool;
the warning banner covers this. Documented in plan.md.

## Live pages (php -S + dev DB)

- `/`, `/standings`, `/stats/leaders`, `/stats/luck`,
  `/history/teammoney` — all 200 after the refactor; teammoney ledger
  figures consistent with the former constants (per-win $3.01 ⇒ pot =
  12×$75 + fines under 0.25/84 split).
- Fake commissioner session (E2E recipe note: the session **id must be
  a PHP-generated-format id** — a short handcrafted id like
  `claudetest1` is silently rejected by Symfony's strict session
  handler, cookie cleared, and every admin page 302s):
  - `/admin/seasons` lists 35 seasons with verified badges.
  - Edit 1998: posted entry fee 50 + `rule_k_fg60=10` + notes →
    persisted exactly (unposted scoring fields stored as null =
    not awarded, as designed — the real form posts every field).
    Restored to seeded values afterwards.
  - Clone: created 2027 unverified with copied values; transpoints
    insert correctly skipped the 12 pre-existing 2027 rows. Test row
    deleted afterwards.
  - `/admin/seasons/2027` rendered the team-budgets grid via the
    teamnames-fallback join. `/admin/scores` shows the overwrite
    warning.

## Deploy notes

- Run `php bin/console doctrine:migrations:migrate` on prod.
- Reprocessing any pre-2024 week on prod will now (correctly) apply
  that season's configured rules — only do so deliberately, after the
  season's rules are verified in `/admin/seasons`.
- The FG60=10 backfill extends to 1992 as a best guess; Josh corrects
  older seasons (and all other rediscovered rules) via
  `/admin/seasons`, flipping `verified` as seasons are confirmed.
