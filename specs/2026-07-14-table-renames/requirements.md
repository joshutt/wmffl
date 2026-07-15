# Phase 7 — Reclaim canonical table names: Requirements

## Goal

Phase 6 dropped the superseded `activations`, `players`, and `injuries`
tables. This phase renames their replacements back to those canonical short
names, retiring the `revised*`/`new*` prefixes for good:

| Current name         | Canonical name | Files referencing (at branch start) |
|----------------------|----------------|--------------------------------------|
| `revisedactivations` | `activations`  | 30 |
| `newplayers`         | `players`      | 69 |
| `newinjuries`        | `injuries`     | 6  |

Phase 10 (boxscores redesign) depends on these final names, so this lands
before it.

## Scope (decided 2026-07-14)

Full roadmap scope — all of:

1. The three `RENAME TABLE` operations.
2. Entity class renames: `App\Entity\RevisedActivation` →
   `App\Entity\Activation`, `App\Entity\NewInjury` → `App\Entity\Injury`
   (both names were freed by Phase 6's dead-entity deletion).
   `App\Entity\Player` keeps its class name; only its `#[ORM\Table]`
   attribute changes.
3. FK constraint renames — MySQL/MariaDB cannot rename a constraint, so
   each is a `DROP FOREIGN KEY` + `ADD CONSTRAINT`:
   - `FK_newinjuries_newplayers` (on `newinjuries` → `newplayers`)
     becomes `FK_injuries_players`
   - `ir_newplayers_playerid_fk` (on `ir` → `newplayers`)
     becomes `ir_players_playerid_fk`
4. Regenerate `scripts/database/schema.sql` from the final schema
   (mariadb-dump, matching the existing file's format).
5. Update `specs/roadmap.md` (Phase 7 → Done).

Out of scope: any behavior change. This is a pure rename; every query,
page, and script must behave identically afterwards.

## Decisions

- **One combined Doctrine migration** (not one per rename): a single
  migration performs all three `RENAME TABLE`s plus the two FK
  drop/re-adds atomically in one `doctrine:migrations:migrate` run.
  Code sweeps are still split across commits (one table per commit) for
  reviewability, but the DB transition is a single step.
- **Dev DB is renamed during development**: the migration runs against
  the dev DB as soon as it's written (Group 1). Between that point and
  the end of the code sweeps, dev pages touching not-yet-swept tables
  will error — expected and acceptable in dev.
- **Prod deploys as one unit**: the migration and the fully-swept code
  deploy together in the same maintenance window, ideally off-season.
  There is no intermediate prod state.
- **Validation depth**: PHPUnit suites + zero-references grep sweep +
  fake-session E2E smoke of the key affected pages (see
  `validation.md`).

## Context / constraints

- Verified at branch start: `activations`, `players`, and `injuries` do
  not exist in the dev DB (Phase 6's drops are applied), so the renames
  cannot collide. Nearby names `offeredplayers`, `playerscores`,
  `weeklyplayerscores` are unrelated and untouched.
- `RevisedActivation` and `NewInjury` are referenced by class name only
  in their own files — all data access to these tables elsewhere is raw
  SQL (DBAL/mysqli) — so the class renames are self-contained file
  renames.
- **Do not edit already-executed migrations.** Older migrations (e.g.
  `Version20260118000000.php`) mention the old table names in comments
  or executed SQL; they must stay byte-identical. The final grep sweep
  excludes `symfony-app/migrations/` and `specs/` archives.
- Legacy `football/` files and `scripts/` use mysqli with inline SQL;
  the sweep there is textual. Use word-boundary matching — the old
  names (`revisedactivations`, `newplayers`, `newinjuries`) are
  distinctive strings with no false-positive neighbors.
- The roadmap's earlier "one rename per commit, DB + code together per
  commit" guidance is superseded by the combined-migration decision
  above (per-commit deployability is not needed since prod gets one
  window anyway).
