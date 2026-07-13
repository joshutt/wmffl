# Requirements — Table cleanup (Phase 6)

Branch: `table-cleanup`. One PR. See `plan.md` for task groups and
`validation.md` for merge criteria.

## Goal

Retire the legacy DB tables superseded by a `new*`-prefixed replacement
(`players`, `activations`, `injuries`), per roadmap Phase 6, and audit
the rest of the schema for similar dead weight — before the final
decommission phase has to reason about the final schema.

## Scope decisions (agreed 2026-07-12)

1. **Three table pairs are in scope**, not just the `players`/`newplayers`
   pair the roadmap entry named. The audit (live DB + code grep,
   documented in `plan.md`) also found `activations`/`revisedactivations`
   and `injuries`/`newinjuries` as legacy/current pairs; all three get
   resolved in this phase since they're the same shape of problem.
2. **`injuries` is merged into `newinjuries`, not just dropped.** The two
   tables are complementary (2010–2019 vs. 2020–2025, no overlap, no
   orphans), not duplicates — `newinjuries` gets the old rows inserted
   (status vocabulary converted, `details` column widened) and then
   `injuries` is dropped. Losing 2010–2019 injury history is not
   acceptable.
3. **`players` and `activations` are straight drops after porting their
   remaining readers.** Verified against the live DB: every `players`
   row exists in `newplayers` (id + name match), and
   `revisedactivations` fully supersedes `activations` (same seasons,
   more of them, plus 2005–06 rows the old table lacks). No merge
   needed — just point the last few readers at the new table first.
4. **Reclaiming the canonical short table names is OUT of this PR.**
   `plan.md`'s audit also worked out what a `newplayers`→`players` (etc.)
   rename sweep would touch (~90+ files total across all three), but
   that's a separate, larger mechanical change with its own
   deploy-coordination risk. Bundling it here would violate the
   roadmap's own "small enough to land as its own PR" rule. It's
   recorded as a deferred follow-up in `plan.md` so the audit isn't
   lost, but this phase leaves the tables named `newplayers`,
   `revisedactivations`, `newinjuries`.
5. **Validation level: same bar as Phase 4/5** — automated tests +
   fake-session manual E2E + legacy-parity spot-checks + a grep gate for
   leftover references to the dropped tables. No additional
   backup/dry-run requirement beyond that; the live-DB audit already
   verified row-level safety (id coverage, zero orphans, zero
   overlapping keys) before any migration is written. See
   `validation.md`.
6. **Ambiguous tables found during the schema audit (task group 4) are
   documented, not dropped.** This phase only removes tables proven
   dead or superseded by the audit; anything unclear goes into
   `audit.md` for a future phase to decide.

## Context

### The three table pairs

| Old table | New table | Old data | New data | Disposition |
|---|---|---|---|---|
| `activations` | `revisedactivations` | 2001–2008, 1,046 rows (wide format) | 2001–2025, 62,686 rows (tall format, incl. 2005–06) | Port 2 boxscore pages, drop `activations` |
| `players` | `newplayers` | 6,302 rows | 15,743 rows | Port 6 frozen history pages + delete 1 orphan file, drop `players` |
| `injuries` | `newinjuries` | 2010–2019, 73,482 rows | 2020–2025, 56,975 rows | Merge old rows into `newinjuries` (vocabulary + column-width conversion), drop `injuries` |

Full per-table findings, including the exact code readers and the
verification queries run against the live DB, are in `plan.md`'s
Findings section — not duplicated here.

### Code readers being migrated

- `football/history/2005Season/boxscores.php`,
  `football/history/2006Season/boxscores.php` — wide→tall
  `activations`→`revisedactivations` join rewrite.
- Six frozen 2003–2008 history pages (`2003Season/leaders.php`,
  `2003Season/protectioncost2004.php`, `2004Season/protectioncost2005.php`,
  `2005Season/protectioncost.php`, `2006Season/protectioncost2007.php`,
  `2008Season/protectioncost.php`) — `players`→`newplayers` table +
  column rename (`Position`→`pos`, `NFLTeam`→`team`, `StatID`→`flmid`).
- `symfony-app/public/images/players/playerstats.php` — dead orphan from
  the images-move commit (`0ad1441`); deleted outright, not ported (its
  functionality already lives in `StatsController`/`StatsRepository`).
- `scripts/database/transactionQueries.sql` — reference doc, not
  runtime code; updated or deleted for hygiene.
- `App\Entity\Injury` + `App\Enum\InjuryStatusEnum` — dead Symfony code
  (maps the `injuries` table, no other references); deleted.

### Schema audit (task group 4)

Sweep `scripts/database/schema.sql` for other legacy-vs-current pairs
or orphans beyond the three already found. `tmp_players` (566 rows) and
`tmp_scan` (611 rows) are already-identified drop candidates (no code
references at all). Output goes to `audit.md` in this spec directory:
a definitive keep/drop list for every table, so the eventual
decommission phase isn't starting from scratch.

### Shared/technical context

- All three drops/merges are Doctrine migrations in
  `symfony-app/migrations/`, run via
  `bin/console doctrine:migrations:migrate` at deploy — never ad-hoc
  SQL, matching how `protections.deadline` and other Phase 4/5 schema
  changes were shipped.
- No DB obstacles found: no triggers touch these tables, the one view
  (`team_wins`) doesn't reference any of them, and the only FKs into
  this set are `newinjuries→newplayers` and `ir→newplayers` (unaffected
  by these drops).
- Deploy timing: off-season (July, no live scoring), same consideration
  as the rest of this migration effort — these are irreversible
  `DROP TABLE` operations against the live schema.

## Non-goals

- Renaming `newplayers`/`revisedactivations`/`newinjuries` back to their
  canonical short names — deferred follow-up, see `plan.md`.
- Dropping any table the schema audit can't confirm is dead or
  superseded — those get documented in `audit.md`, not removed.
- Changing `newinjuries`' status vocabulary or any other live-table
  business logic beyond what's needed to accept the merged historical
  rows.
