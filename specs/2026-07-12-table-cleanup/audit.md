# Schema audit — every table, keep/drop disposition

Produced by Phase 6 task group 4 (2026-07-12). Method: live-DB table
list (63 tables incl. one view) cross-referenced against a code grep of
`football/`, `symfony-app/{src,templates,config,tests,migrations}/`,
`scripts/`, `lib/`, `test/` (excluding the `scripts/database/schema.sql`
dump itself). Reference counts are *files containing the table name*;
counts for English-word names (`team`, `stats`, `user`, `paid`, …) are
inflated by prose matches, so every low-count table's referencing files
were inspected individually. Row counts are from the live DB on
2026-07-12, after this phase's migrations ran.

Note: `Version20260118000000.php` (the schema-import migration) and the
`App\Entity\*` schema-mapping entities mention nearly every table; a
table whose *only* references are those two is not used at runtime.

## Dropped in this phase (Phase 6 migrations)

| Table | Rows at drop | Why |
|---|---|---|
| `activations` | 1,046 | Superseded by `revisedactivations` (verified set-equal for 2005–06, the only seasons still displayed); last readers ported |
| `players` | 6,302 | Superseded by `newplayers` (full id coverage); last readers ported |
| `injuries` | 73,482 | 2010–2019 rows **merged into** `newinjuries` (status vocabulary converted, `details` widened to varchar(50)), then dropped |
| `tmp_players` | 566 | One-off scratch table, zero code references |
| `tmp_scan` | 611 | One-off scratch table, zero code references |

## Drop candidates for the final decommission phase (dead, not dropped here)

Zero code references anywhere; listed rather than dropped because this
phase only committed to the `tmp_*` drops:

| Table | Rows | Notes |
|---|---|---|
| `expandAvailable` | 0 | Empty, never referenced |
| `weeklyplayerscores` | 0 | Empty, never referenced |
| `forum_seq` | 1 | PEAR DB_DataObject sequence for `forum`; nothing inserts into `forum` anymore (read-only trash-talk archive), so the sequence is dead |

## Entity-mapping only — no runtime reader, holds historical data (keep for now)

Referenced only by their `App\Entity` class and the schema-import
migration comment. Nothing reads or writes them at runtime today, but
they hold league history (or may be seasonal); the decommission phase
decides whether to archive or keep:

| Table | Rows | Notes |
|---|---|---|
| `chat` | 1,919 | Legacy chat archive; no chat feature in either app anymore |
| `expansionLost` | 10 | Expansion-draft history (rare seasonal event) |
| `expansionpicks` | 20 | Expansion-draft history |
| `expansionprotections` | 80 | Expansion-draft history |
| `nfltransactions` | 42,189 | NFL transaction feed archive; no importer in the repo writes it today |
| `playeroverride` | 2 | Manual score-override mechanism; entity only |
| `playerteams` | 1,577 | Player↔NFL-team history; superseded at runtime by `nflrosters` |
| `protectionallocation` | 0 | Empty; entity only |
| `rankedvote` | 18 | Old vote data; `ballot`/`draftvote` are the live voting tables |

## In active use (runtime readers found; keep)

Grouped for readability; every table below has at least one real query
in legacy or Symfony code.

- **Core league data**: `team`, `teamnames`, `division`, `owners`,
  `user`, `years`, `weekmap`, `schedule`, `roster`, `config`,
  `season_flags`, `paid` (money pages), `titles`, `issues`
- **Players/scoring**: `newplayers`, `playerscores`, `stats`,
  `revisedactivations`, `newinjuries`, `ir`, `playeroverride`*(entity
  only — listed above)*, `positioncost`, `protectioncost`,
  `protections`, `protectionallocation`*(entity only — listed above)*
- **NFL reference data**: `nflteams`, `nflgames`, `nflrosters`,
  `nflstatus`, `nflbyes`
- **Transactions/trades/waivers**: `transactions`, `transpoints`,
  `trade`, `offer`, `offeredpicks`, `offeredplayers`, `offeredpoints`
  (all three read by `football/transactions/trades/loadTrades.inc.php`),
  `waiverorder`, `waiverpicks`, `waiveraward`
- **Draft**: `draftdate`, `draftpicks`, `draftvote`, `ballot`,
  `draftPickHold` (`football/services/picks.php`), `draftclockstop`
  (`football/services/clock.class.php`), `gameplan`
- **Content**: `articles`, `comments`, `images`, `forum` (read-only:
  homepage trash-talk widget + `football/forum/`)
- **View**: `team_wins` — joined by `football/history/common/moneyUtil.php`
- **Infrastructure**: `doctrine_migration_versions`

## Renamed-table follow-up

`newplayers`, `revisedactivations`, `newinjuries` keep their awkward
names in this phase; the rename back to `players`/`activations`/
`injuries` is the deferred follow-up documented in `plan.md`.
