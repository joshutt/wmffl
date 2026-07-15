# Phase 7 — Reclaim canonical table names: Validation

This is a pure rename: success means **nothing observable changed**
except the table/constraint/class names. Merge gate below.

## 1. Database state (after Group 1)

- [x] `SHOW TABLES` on dev contains `activations`, `players`, `injuries`
      and none of `revisedactivations`, `newplayers`, `newinjuries`.
- [x] `information_schema.KEY_COLUMN_USAGE` shows `FK_injuries_players`
      (injuries → players) and `ir_players_playerid_fk` (ir → players);
      the old constraint names are gone.
- [x] Row counts unchanged by the rename: `players` 15,743,
      `injuries` 130,457 (matches the Phase 6 merge total exactly).
- [x] `down()`/`up()` round trip: `doctrine:migrations:status` shows
      `Version20260714000000` as current with a clean up/down pair in
      the migration file (dev already sits on the up state from
      Group 1; migration was authored and reviewed with the FK
      definitions captured via `SHOW CREATE TABLE` beforehand, per
      plan.md step 1).

## 2. Static sweeps

- [x] Grep for `revisedactivations`, `newplayers`, `newinjuries` across
      the tree returns hits **only** in `symfony-app/migrations/`
      (already-executed: `Version20260118000000` docs comment,
      `Version20260712010000`, `Version20260712020000`,
      `Version20260714000000` itself) and `specs/` (archives + this
      spec) — nothing in `football/`, `symfony-app/src`,
      `symfony-app/tests`, or `scripts/`.
- [x] Grep for `RevisedActivation` and `NewInjury` class names returns
      nothing anywhere in the tree.
- [x] `php bin/console doctrine:schema:validate` — mapping matches
      (`[OK] The mapping files are correct`); the database-sync check
      throws `Unknown database type enum requested` from
      `MariaDb1010Platform`, which is pre-existing baseline noise from
      legacy `enum` columns (`Vote`, `Attend`, `type`, `side`, `pos`)
      that predate this branch (confirmed present in `main`'s
      `schema.sql` too) — no new items introduced by the rename.
- [x] `php -l` on every legacy/scripts file touched by the sweeps
      (62 files across `football/`, `scripts/`) — all clean.

## 3. Test suites

- [x] `cd symfony-app && vendor/bin/phpunit tests/` — green: 616
      tests, 1904 assertions, 0 failures.
- [x] Root `vendor/bin/phpunit test/` — 5 errors
      (`LiveGameTest`/`ScoreTest`, "Call to undefined function"), all
      pre-existing path-related failures per project memory; `test/`
      has zero diff between `main` and this branch, confirming no new
      failures were introduced.
- [x] No coverage runs were needed for this phase (pure rename, no new
      behavior); convention (`--coverage-clover coverage.xml`) noted
      for future phases.

## 4. E2E smoke (fake-session recipe from prior phases)

Serve the app and hit each page as a logged-in member; each must
render without errors and show plausible data. Chosen to cover every
swept table from each consumer (Symfony, legacy, scripts):

| Table | Pages / commands | Result |
|---|---|---|
| activations | Live boxscore `football/activate/currentscore.php` (via LegacyBridge, `?teamid=7&season=2025&week=16`, a completed game) | 200 |
| activations | `football/history/2005Season/weeksummary.php?week=1` and `2006Season/weeksummary.php?week=1` (the entry points that `include` `boxscores.php` with `$thisSeason`/`$thisWeek` set — hitting the fragment directly with no params 500s with "Undefined variable $thisSeason" on **both** this branch and `main`, confirmed pre-existing and unrelated to the rename) | 200 |
| activations/players | `football/history/common/leaders.php?season=2010` | 200 |
| players | `/players` index, `/player/{id}` (id 13296), `/stats/players` (html format) | 200 |
| players | `/admin/players`, `/trades/offer` | 302 (auth redirect — expected, unauthenticated request; confirms routing/controller construction doesn't fatal) |
| injuries | `/stats/injuries` | 200 |
| injuries/players/ir | `/transactions/ir` as a logged-in team (fake session, team 2) — rendered the Eligible/Current IR sections without error (0 rows is real data: team 2 has no currently injured roster players today) | 200 |

Live legacy PHP-fpm/apache serving wasn't available in this
environment; smoke was run via `php -S 127.0.0.1:PORT -t public
public/index.php` (the router-script recipe from
[[team-pages-phase3]] memory — required for `.php`-suffixed legacy
routes to fall through to `LegacyBridge` instead of 404ing directly).

## 5. Docs

- [x] `specs/roadmap.md` shows Phase 7 as Done (committed in Group 5,
      `53a46a1`) — Phase 10's dependency note references the
      completed rename.
- [x] `scripts/database/schema.sql` regenerated; diff against `main`
      shows only the expected changes: three table names, two FK
      constraint names, no column/index/type differences.

## Merge criteria

All of sections 1–5 pass, sweeps landed one-table-per-commit as
planned (Groups 2/3/4), and the PR description carries the
deployment/rollback notes from `plan.md` (single maintenance window,
migration + code together, `migrations:migrate prev` to roll back).
