# Phase 7 — Reclaim canonical table names: Validation

This is a pure rename: success means **nothing observable changed**
except the table/constraint/class names. Merge gate below.

## 1. Database state (after Group 1)

- `SHOW TABLES` on dev contains `activations`, `players`, `injuries`
  and none of `revisedactivations`, `newplayers`, `newinjuries`.
- `information_schema.KEY_COLUMN_USAGE` shows `FK_injuries_players`
  (injuries → players) and `ir_players_playerid_fk` (ir → players);
  the old constraint names are gone.
- Row counts unchanged by the rename (spot-check `players` and
  `injuries` — the latter should still be 130,457 from the Phase 6
  merge).
- `down()` tested once on dev: migrate prev → names revert → migrate
  back up.

## 2. Static sweeps

- Grep for `revisedactivations`, `newplayers`, `newinjuries` across
  the tree returns hits **only** in `symfony-app/migrations/`
  (already-executed, untouched) and `specs/` (archives + this spec).
- Grep for `RevisedActivation` and `NewInjury` class names returns
  nothing.
- `php bin/console doctrine:schema:validate` — mapping in sync (or
  the same known baseline noise as before the branch, no new items).
- `php -l` on every legacy/scripts file touched by the sweeps.

## 3. Test suites

- `cd symfony-app && vendor/bin/phpunit tests/` — green.
- Root `vendor/bin/phpunit test/` — no *new* failures (this suite has
  known pre-existing path-related failures; compare against a run
  from `main`).
- PHPUnit coverage runs, when used, use `--coverage-clover
  coverage.xml` (project convention).

## 4. E2E smoke (fake-session recipe from prior phases)

Serve the app and hit each page as a logged-in member; each must
render without errors and show plausible data. Chosen to cover every
swept table from each consumer (Symfony, legacy, scripts):

| Table | Pages / commands |
|---|---|
| activations | Live boxscore `football/activate/currentscore.php` (via LegacyBridge) for a completed past game; `football/history/2005Season/boxscores.php` and `2006Season/boxscores.php` (Phase 6 made these byte-identical — they must still render the same lineups); a history leaders page under `football/history/common/` |
| players | `/players` index + search; `/player/{id}` profile (use a Phase 2 test player id); `/admin/players` edit form loads; `/stats/players` (html + csv formats); `/trades/offer` builder lists rosters; one frozen 2003–2008 per-season history page |
| injuries | `/stats/injuries` report; `/transactions/ir` page + one add/remove round-trip |
| scripts | Dry-run (or run against dev) the livescore/logscores/updateactivations scripts far enough to prove their queries parse and execute |

## 5. Docs

- `specs/roadmap.md` shows Phase 7 as Done and Phase 10's dependency
  satisfied.
- `scripts/database/schema.sql` regenerated; diff against the old file
  shows only the expected name changes (tables, FK constraints), no
  column/index differences.

## Merge criteria

All of sections 1–5 pass, sweeps are one-table-per-commit as planned,
and the PR description carries the deployment/rollback notes from
`plan.md` (single maintenance window, migration + code together).
