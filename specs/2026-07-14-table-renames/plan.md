# Phase 7 — Reclaim canonical table names: Plan

One combined migration up front, then one code-sweep commit per table,
then schema/docs, then validation. See `requirements.md` for decisions
and `validation.md` for the merge gate.

## Group 1 — Combined rename migration

1. Capture `SHOW CREATE TABLE newinjuries` and `SHOW CREATE TABLE ir`
   to get the exact FK column definitions and referential actions
   before writing the migration.
2. Write `Version20260714000000` in `symfony-app/migrations/`:
   - `RENAME TABLE revisedactivations TO activations,
     newplayers TO players, newinjuries TO injuries` (one statement —
     atomic in MySQL/MariaDB; renames automatically re-point the FKs,
     only the constraint *names* stay stale)
   - `ALTER TABLE injuries DROP FOREIGN KEY FK_newinjuries_newplayers`,
     `ADD CONSTRAINT FK_injuries_players FOREIGN KEY ... REFERENCES
     players ...` (matching the captured definition)
   - `ALTER TABLE ir DROP FOREIGN KEY ir_newplayers_playerid_fk`,
     `ADD CONSTRAINT ir_players_playerid_fk FOREIGN KEY ... REFERENCES
     players ...`
   - `down()` reverses everything (rename back, restore old FK names)
3. Run it against the dev DB (`php bin/console
   doctrine:migrations:migrate`). Verify: `SHOW TABLES` shows the three
   canonical names and none of the old ones; `information_schema`
   shows the two new constraint names.
4. Commit. **Note:** from here until Group 4 lands, dev pages that
   query not-yet-swept tables will error. That's expected; don't
   "fix" it by touching the DB.

## Group 2 — Code sweep: activations (~30 files)

1. Rename `App\Entity\RevisedActivation` → `App\Entity\Activation`
   (file rename + class name + `#[ORM\Table(name: 'activations')]`).
   No other file references the class name.
2. Sweep `revisedactivations` → `activations` in `symfony-app/src/`
   and `symfony-app/tests/` (SQL strings in services/repositories,
   test fixtures/expectations).
3. Sweep legacy: `football/activate/` (incl. `currentscore.php` /
   `scoreFunctions.php`), `football/history/common/` + per-season
   leaders pages, `football/history/2005Season/boxscores.php` and
   `2006Season/boxscores.php` (Phase 6 rewrote these against
   `revisedactivations`).
4. Sweep `scripts/` (livescore, logscores, updateactivations).
5. `grep -ri revisedactivations` over `football/ symfony-app/src
   symfony-app/tests scripts/` returns nothing; spot-check one page.
   Commit.

## Group 3 — Code sweep: players (~69 files)

1. `App\Entity\Player`: change `#[ORM\Table(name: 'newplayers')]` to
   `'players'`. Class name unchanged.
2. Sweep `newplayers` → `players` in `symfony-app/src/` (~17 files:
   PlayerRepository, StatsRepository, trade services, IR, etc.) and
   `symfony-app/tests/`.
3. Sweep legacy `football/` including the six frozen 2003–2008
   per-season history pages (ported to `newplayers` in Phase 6) and
   any remaining rosters/boxscore includes.
4. Sweep `scripts/`.
5. Match the exact token `newplayers` only — do not touch
   `offeredplayers`/`playerscores`/`weeklyplayerscores`, executed
   migrations, or `specs/` archives. Grep-verify zero remaining
   references outside those exclusions. Commit.

## Group 4 — Code sweep: injuries (6 files)

1. Rename `App\Entity\NewInjury` → `App\Entity\Injury` (file + class +
   `#[ORM\Table(name: 'injuries')]`). No other file references the
   class name.
2. Sweep `newinjuries` → `injuries` in the remaining files
   (InjuryReportService and the handful of others found by grep).
3. Grep-verify, commit.

## Group 5 — Schema dump + roadmap

1. Regenerate `scripts/database/schema.sql` with mariadb-dump
   (`--no-data`, same invocation style as the existing header) from
   the renamed dev DB.
2. Full-tree grep for all three old names — expect hits only in
   `symfony-app/migrations/` (historical, untouched) and `specs/`
   (archives + this spec). Anything else is a straggler to fix.
3. Update `specs/roadmap.md`: move Phase 7 into Done (note the
   combined-migration decision); un-block the Phase 10 dependency
   note. Commit.

## Group 6 — Validation pass

Run everything in `validation.md`: both PHPUnit suites,
`doctrine:schema:validate`, and the fake-session E2E smoke list.
Fix anything found, then the branch is ready for PR.

## Deployment (for the PR description / maintenance window)

- Prod: put the site in the window, deploy code + run the single
  migration together, smoke-test, done. No intermediate state.
- Rollback: `migrations:migrate prev` (the `down()` restores old names
  and FK constraint names) + redeploy previous code.
