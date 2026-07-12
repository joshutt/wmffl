# Validation — Table cleanup (Phase 6)

The branch is mergeable when everything below passes. Validation level
agreed 2026-07-12: same bar as Phase 4/5 — automated tests + fake-session
manual E2E + legacy-parity spot-checks + a grep gate — with no extra
backup/dry-run requirement, since the live-DB audit in `plan.md` already
verified row-level safety (full id coverage, zero orphans, zero
overlapping keys) before any migration was written.

**Outcome (2026-07-12): all sections pass.** Checkboxes below are marked
with results; deviations from the plan's assumptions are called out
inline and summarized at the bottom.

## 1. Automated tests

- [x] `cd symfony-app && vendor/bin/phpunit tests/ --coverage-clover coverage.xml`
  passes — 519 tests, 1,557 assertions, green after the entity/enum
  deletions.
- [x] Migration assertions cover the merge: `Version20260712020000`
  `postUp()` fails the migration unless exactly 73,482 rows land in
  2010–2019 and no row has an unmapped status. Verified on the dev DB:
  total 130,457; per-status counts match the old letter distribution
  exactly (P 12,416→Probable, Q 19,170→Questionable, D 1,197→Doubtful,
  O 12,837→Out, I 25,937→IR, S 1,925→Suspended, matching
  `InjuredReserveService::IR_STATUSES` vocabulary).
- [x] `details` widened to varchar(50) before the insert; longest merged
  value (39 chars) intact, 8,963 rows over the old 32-char limit
  confirmed un-truncated.
- [x] Root legacy suite (`vendor/bin/phpunit test/`): same 5 pre-existing
  include-path errors (`ScoreTest`/`LiveGameTest` undefined functions),
  no new failures.

## 2. Manual E2E (fake-session recipe from Phases 1–5)

Run against the dev DB with `php -S 127.0.0.1:8899 -t public
public/index.php` (router script, per the Phase 3 gotcha).

Ported pages render with real data and no PHP errors:
- [x] `2005Season/boxscores.php` + `2006Season/boxscores.php` —
      2005 w3 and 2006 w3 rendered **byte-identical** before/after the
      tall-format rewrite (CLI harness; 2006 w5/w4/w7 hit a *pre-existing*
      PHP 8 fatal — `int + "X"` when a penalty row is rendered — present
      on main, unrelated to the join change, left as-is)
- [x] All six ported history pages render 200 with data through the
      LegacyBridge. **They all 500ed on main** (see deviations below),
      so "byte-comparable before/after" was measured at the SQL level
      instead of rendered HTML.
- [x] `/players` index (50 player rows) and `/player/8336` (Prater) —
      unaffected by the `players` drop
- [x] `/transactions/ir` — full add/remove round-trip exercised with a
      seeded 2026 IR-status row for a team-2 player (playerid 13444):
      Add → `ir` row inserted, Remove → `dateoff` set; JSON messages
      correct; test rows cleaned up afterwards
- [x] `/team/2/roster` + `/team/2/schedule` — 200, no error markers.
      Note: every `newinjuries` join in the codebase is pinned to the
      *current* week, so no page actually displays 2010–2019 injuries;
      the merged history was verified at the DB level instead (see
      section 3)
- [x] `/stats/injuries` — both tabs (`currentLists`, `fillList`) render;
      `fillList` empty is correct offseason behavior (query pinned to
      current week 2026 w0), unaffected by the merge
- [x] `/stats/players?format=csv&pos=QB` — 79 lines, unaffected

Confirm removed:
- [x] `symfony-app/public/images/players/playerstats.php` deleted. It
      now falls into the same "Unhandled legacy mapping" 500 as *any*
      unknown URL (site-wide LegacyBridge fall-through behavior, a known
      pre-existing follow-up from Phase 3) — no leakage, no dangling
      executable file; production Apache serves /images/* statically.

## 3. Legacy parity spot-checks

- [x] Boxscore point totals 2005 w3 / 2006 w3: byte-identical rendered
      output. Underlying data verified set-equal for **all** 2005–2006
      weeks (4,440 tall rows = 296 wide rows × 15 slots, zero
      mismatches either direction, zero duplicate keys).
- [x] Player names/positions on the six ported pages: SQL-level
      old-vs-new diffs captured before the drop. Identical rows except
      documented deltas (see deviations).
- [x] Injury vocabulary conversion: per-status row counts match the old
      distribution exactly; spot row (Campanaro 2015 w5, 39-char
      details, IR) intact. No UI shows 2010–2019 weeks, so DB-level
      verification is the parity check for the merge.

## 4. Grep gate

- [x] Zero remaining references to the bare `activations`, `players`,
      or `injuries` tables outside `symfony-app/migrations/` history
      (only prose comment matches remain; `scripts/database/schema.sql`
      regenerated from the post-migration schema so the dump no longer
      describes them).
- [x] `App\Entity\Injury` + `App\Enum\InjuryStatusEnum` deleted, zero
      references. Also deleted `App\Entity\Activation` (mapped the
      dropped `activations` table; schema-mapping-only, missed by the
      plan's findings). `doctrine:schema:validate --skip-sync`: mappings
      correct.
- [x] `scripts/database/transactionQueries.sql` updated to `newplayers`.

## 5. Schema audit deliverable

- [x] `audit.md` written: disposition for all 63 DB objects — 5 dropped
      this phase, 3 zero-reference future drop candidates
      (`expandAvailable`, `weeklyplayerscores`, `forum_seq`), 9
      entity-mapping-only keepers with historical data, the rest in
      active use.
- [x] `tmp_players` + `tmp_scan` dropped (`Version20260712030000`;
      confirmed no code references).
- [x] Ambiguous tables listed in `audit.md`, not dropped.

## 6. No-regression

- [x] Smoke pass with fake session: `/`, `/players`, `/player/8336`,
      `/stats/injuries`, `/stats/players` CSV, `/transactions`,
      `/transactions/ir`, `/standings`, `/teams`, `/team/2/roster`,
      `/team/2/schedule` — all 200, zero exception/fatal markers.
- [x] `git status` clean after `git add -A` on every commit (deletions
      swept properly).

## 7. Merge checklist

- [x] Sections 1–6 pass.
- [x] `specs/roadmap.md` updated (Phase 6 → Done with summary; Phase 7
      rename follow-up already recorded).
- [ ] Code-review pass (`/code-review`) and findings addressed.
- [x] Migrations executed on the dev DB in version order
      (`Version20260712000000` activations → `010000` players →
      `020000` injuries merge → `030000` tmp tables); groups are
      independent, deploy window is the current off-season.
- [ ] PR description lists the three dropped tables, the merged row
      counts, and links to `audit.md`.

## Deviations from the plan's assumptions (all verified, none blocking)

1. **All six `players`-reading history pages were already dead on main**
   (HTTP 500): the five protectioncost pages' comma-join + `LEFT JOIN`
   mix broke on MySQL 5+ ("Unknown column p.playerid in ON clause"), and
   all six trip PHP 8 undefined-variable warnings (`$isin`, `$page`,
   `$off`/`$def`) that the LegacyBridge escalates to ErrorException.
   The port fixes these (explicit JOINs, `utils/start.php` bootstrap,
   array initialization) — required by section 2's "render with real
   data and no PHP errors".
2. **"Byte-comparable" did not hold for `players`→`newplayers`** —
   `newplayers` normalizes names (LaDainian Tomlinson, Maurice
   Jones-Drew, Domanick Davis→Williams, team-offense pseudo-players →
   real city/nickname) and corrects one position (Colston TE→WR, his
   2008 extra cost displays +6 instead of +1; WR is factually right).
3. **`2003Season/leaders.php` groups by the `revisedactivations` slot**
   rather than `newplayers.pos`: the old `players` snapshot was neither
   2003-true nor current, `newplayers.pos` reflects later career position
   changes (26 of 100 lines would shift), while the activation slot is
   the position each player actually scored at in 2003 (6 lines shift,
   all DL/LB reallocations within the same teams). Exactly 10 rows per
   position, so the page's fixed 10-row loop stays aligned.
4. **`App\Entity\Activation` existed and mapped `activations`** — not in
   the plan's findings; deleted alongside the drop (no references).
5. **`activations` drop-safety was re-verified wholesale**: instead of
   only spot-checking two weeks, all 2005–2006 wide rows were unpivoted
   and set-compared against the tall table (zero diffs both directions).
6. A scratchpad `mysqldump` of all five dropped tables was taken before
   migrating the dev DB (belt-and-suspenders; production still deploys
   via the migrations with its own backup regime).
