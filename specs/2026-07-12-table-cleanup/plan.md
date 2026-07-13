# Plan — Phase 6: Table cleanup

Branch `table-cleanup`, one PR. Covers Phase 6 as written in the roadmap
(retire `players`, delete `playerstats.php`, schema audit), extended to
the other two legacy/current table pairs the audit turned up
(`activations`/`revisedactivations`, `injuries`/`newinjuries`): migrate
every remaining use of the old table to the new one, then drop the old
table.

**Renaming the surviving tables back to their canonical short names
(`newplayers`→`players` etc.) is explicitly OUT of this PR** — decided
2026-07-12. It's a ~90+ file mechanical sweep across legacy and Symfony
with its own deploy-coordination risk, and the roadmap's own rule is
that each phase should be small enough to land as its own PR. It's
recorded below as a deferred follow-up so the audit work isn't lost.

## Findings (2026-07-12 audit, live DB + code grep)

| Old table | New table | Old data | New data | Old-table code users |
|---|---|---|---|---|
| `activations` | `revisedactivations` | 2001–2008, 1,046 rows (wide: one row per team-week, `HC`/`QB`/`RB1`… columns) | 2001–2025, 62,686 rows (tall: one row per slot, incl. 4,440 rows for 2005–06) | `football/history/2005Season/boxscores.php`, `football/history/2006Season/boxscores.php` (read-only) |
| `players` | `newplayers` | 6,302 rows | 15,743 rows | 6 frozen history pages (below), `symfony-app/public/images/players/playerstats.php`, `scripts/database/transactionQueries.sql` (reference doc, not runtime) |
| `injuries` | `newinjuries` | **2010–2019, 73,482 rows** | **2020–2025, 56,975 rows** | none at runtime — only the dead `App\Entity\Injury` + `App\Enum\InjuryStatusEnum` |

Key facts verified against the live DB:

- **`revisedactivations` fully supersedes `activations`** — same seasons
  and more. The two boxscore pages need their join rewritten from the
  wide format (`p.playerid in (a.HC, a.QB, …)`) to the tall format
  (`a.playerid = p.playerid`); both queries already join `newplayers`.
- **`players` and `newplayers` share one id space**: all 6,302
  `players.PlayerID` values exist in `newplayers.playerid` (6,198 also
  match on lastname; the rest are spelling normalizations). Porting a
  query is a table + column rename: `Position`→`pos`,
  `NFLTeam`→`team`, `StatID`→`flmid`; `FirstName`/`LastName`/`PlayerID`
  match case-insensitively already.
- **`injuries` vs `newinjuries` are complementary, NOT duplicates.**
  `newinjuries` is the correct/live table (all Symfony services, legacy
  `activate/`, and `TeamRepository` use it; FK to `newplayers`), but the
  old `injuries` table holds 2010–2019 history that `newinjuries`
  lacks. So this is a data **merge**, then a drop — not a straight drop.
  Merge feasibility verified: 0 orphan playerids, 0 overlapping
  `(season, week, playerid)` keys. Two conversions needed:
  - `newinjuries.details` is `varchar(32)`; 8,963 old rows exceed that
    — widen to `varchar(50)` first.
  - Status vocabularies differ: old enum `P/Q/D/O/I/S` vs. new words
    (`Probable`, `Questionable`, `Doubtful`, `Out`, `IR`, `Suspended`,
    …). Map on insert: P→Probable, Q→Questionable, D→Doubtful, O→Out,
    I→IR, S→Suspended, matching `InjuredReserveService::IR_STATUSES`
    vocabulary.
- **Dead Symfony code**: `App\Entity\Injury` (maps table `injuries`) and
  `App\Enum\InjuryStatusEnum` (used only by that entity) have no other
  references — delete both. `App\Entity\NewInjury` and
  `App\Entity\RevisedActivation` are schema-mapping-only entities (no
  repository/controller uses); they stay but get renamed in group 5.
- **No DB obstacles**: no triggers; the only view (`team_wins`) touches
  none of these tables; the only FKs into this set are
  `newinjuries→newplayers` and `ir→newplayers` (`RENAME TABLE`
  preserves FKs; constraint names keep "newplayers" — rename them in
  the same migration for hygiene).
- `tmp_players` (566 rows) and `tmp_scan` (611 rows) are referenced by
  no code at all — drop candidates for the audit step.

## Task groups

### 1. Activations: migrate the two boxscore pages, drop `activations`

1. Rewrite the query in `football/history/2005Season/boxscores.php` and
   `football/history/2006Season/boxscores.php` to join
   `revisedactivations` tall-format
   (`join revisedactivations a on a.playerid = p.playerid and …` with
   the existing `a.season`/`a.week`/`a.teamid` predicates; the wide
   column list goes away). Spot-check one 2005 and one 2006 week's
   boxscore output against the old query before/after.
2. Doctrine migration: `DROP TABLE activations`.

### 2. Players: port the frozen history pages, delete the orphan, drop `players`

1. Port the six frozen 2003–2008 history pages from `players` to
   `newplayers` (column renames per the findings above):
   - `football/history/2003Season/leaders.php`
   - `football/history/2003Season/protectioncost2004.php`
   - `football/history/2004Season/protectioncost2005.php`
   - `football/history/2005Season/protectioncost.php`
   - `football/history/2006Season/protectioncost2007.php`
   - `football/history/2008Season/protectioncost.php`
   Spot-check each page renders the same rows (they're frozen seasons,
   so output should be byte-comparable).
2. Delete `symfony-app/public/images/players/playerstats.php` (roadmap
   item 2 — orphaned live `.php` in the public docroot; functionality
   already covered by `StatsController`/`StatsRepository`).
3. Update or delete `scripts/database/transactionQueries.sql` (reference
   SQL that still names `players`).
4. Doctrine migration: `DROP TABLE players`.

### 3. Injuries: merge 2010–2019 history into `newinjuries`, drop `injuries`

1. Delete dead code: `symfony-app/src/Entity/Injury.php`,
   `symfony-app/src/Enum/InjuryStatusEnum.php`.
2. Doctrine migration, in order:
   - `ALTER TABLE newinjuries MODIFY details varchar(50)` (match the old
     column so nothing truncates);
   - `INSERT INTO newinjuries (playerid, season, week, status, details)
     SELECT … FROM injuries` with the status-letter → word CASE map;
   - verify row count (73,482 inserted; total 130,457) inside the
     migration or in validation;
   - `DROP TABLE injuries`.
3. Check pages that join `newinjuries` over historical weeks now show
   sensible data for 2010–2019 (team roster/schedule pages via
   `TeamRepository`, `/stats/injuries` current-week view must be
   unaffected).

### 4. Schema audit (roadmap item 3)

1. Sweep the remaining tables in `scripts/database/schema.sql` for other
   legacy-vs-current pairs or orphans (candidates already spotted:
   `tmp_players`, `tmp_scan` — no code references; drop them in a
   migration). For each remaining table, record whether code references
   it; write the result to `audit.md` in this spec directory so the
   final decommission phase has a definitive keep/drop list.
2. Anything ambiguous gets listed, not dropped — this phase only drops
   tables proven dead or superseded.

### 5. Validation + docs

1. Test suites: root `vendor/bin/phpunit test/` (pre-existing failures
   noted in memory), `symfony-app vendor/bin/phpunit tests/`.
2. Exercise the affected surfaces end-to-end: `/stats/injuries`,
   `/transactions/ir`, a team roster + schedule page, `/players` index,
   a player profile, the 2005/2006 boxscore pages and all six ported
   history pages via the LegacyBridge, one leaders page per era
   (2003, 2007+), `/stats/players` CSV.
3. Grep gate: zero remaining references to the bare `activations`,
   `players`, or `injuries` tables (the ones being dropped) anywhere
   outside `symfony-app/migrations/` history — `revisedactivations`,
   `newplayers`, and `newinjuries` are expected to remain, since the
   rename back to canonical names is deferred (see below).
4. Update `specs/roadmap.md` (move Phase 6 to Done, note the deferred
   rename follow-up) and this spec's `validation.md` with the outcome.

## Deferred follow-up: reclaim canonical table names

Not part of this PR (see decision at the top). Once groups 1–3 land and
have run in production for a while, a later phase should:

1. `RENAME TABLE revisedactivations TO activations` — update ~27 files
   (`football/activate/`, `football/history/common/` + per-season
   leaders, `scripts/` livescore/logscores/updateactivations,
   `symfony-app` services/controllers/tests). Rename
   `App\Entity\RevisedActivation` → `App\Entity\Activation`.
2. `RENAME TABLE newplayers TO players` — update ~64 files (biggest
   sweep: 17 symfony-app src files, legacy `football/` incl. per-season
   history pages, `scripts/`). `App\Entity\Player` just changes its
   `#[ORM\Table]` name. Also rename the FK constraints
   (`FK_newinjuries_newplayers`, `ir_newplayers_playerid_fk`) in the
   migration.
3. `RENAME TABLE newinjuries TO injuries` — update 6 files. Rename
   `App\Entity\NewInjury` → `App\Entity\Injury` (the name freed by
   group 3 of this PR).
4. Do **not** edit already-executed migrations (e.g.
   `Version20260118000000.php` mentions these names in comments only).
5. Regenerate `scripts/database/schema.sql` from the final schema.
6. One rename per commit, DB rename + code sweep deploying together,
   ideally off-season.

## Sequencing / deploy notes

- Groups 1–3 are independent of each other and can land in any order;
  group 4 (schema audit) can run any time.
- Every DB change is a Doctrine migration in `symfony-app/migrations/`
  (`bin/console doctrine:migrations:migrate` at deploy), never ad-hoc
  SQL, so the drop order is reproducible.
- These are irreversible `DROP TABLE` operations against the live
  schema; deploy during the current off-season window (it is July — no
  live scoring running) same as the rest of this migration effort.
