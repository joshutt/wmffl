# Validation — Table cleanup (Phase 6)

The branch is mergeable when everything below passes. Validation level
agreed 2026-07-12: same bar as Phase 4/5 — automated tests + fake-session
manual E2E + legacy-parity spot-checks + a grep gate — with no extra
backup/dry-run requirement, since the live-DB audit in `plan.md` already
verified row-level safety (full id coverage, zero orphans, zero
overlapping keys) before any migration was written.

## 1. Automated tests

- `cd symfony-app && vendor/bin/phpunit tests/ --coverage-clover coverage.xml`
  passes (clover format per project convention — never `--coverage-text`).
- Migration tests/assertions cover:
  - the `newinjuries` merge inserts exactly 73,482 rows (verify final
    count is 130,457) and every status letter maps to the expected word
    (P→Probable, Q→Questionable, D→Doubtful, O→Out, I→IR, S→Suspended,
    matching `InjuredReserveService::IR_STATUSES`);
  - `details` column accepts the widened `varchar(50)` values without
    truncation.
- Root legacy suite (`vendor/bin/phpunit test/` from project root) shows
  no NEW failures (pre-existing include-path failures are known).

## 2. Manual E2E (fake-session recipe from Phases 1–5)

Run against the dev DB with `php -S` through `symfony-app/public/`.

Ported pages render with real data and no PHP errors:
- [ ] `football/history/2005Season/boxscores.php` and
      `2006Season/boxscores.php` — spot-check one week each against
      pre-migration output (tall-format join must match the old
      wide-format numbers)
- [ ] All six ported history pages
      (`2003Season/leaders.php`, `2003Season/protectioncost2004.php`,
      `2004Season/protectioncost2005.php`, `2005Season/protectioncost.php`,
      `2006Season/protectioncost2007.php`, `2008Season/protectioncost.php`)
      — frozen seasons, so output should be byte-comparable before/after
- [ ] `/players` index and a player profile — unaffected by the
      `players` drop
- [ ] `/transactions/ir` — add/remove still works against `newinjuries`
- [ ] Team roster + schedule page (via `TeamRepository`) — historical
      2010–2019 injury data now shows up where it didn't before
- [ ] `/stats/injuries` — both tabs, current-week view unaffected by the
      merge
- [ ] `/stats/players` CSV export — unaffected by the `players` drop

Confirm removed:
- [ ] `symfony-app/public/images/players/playerstats.php` no longer
      exists / returns 404, not phpinfo-style leakage or a fatal error
      from a dangling web-reachable file

## 3. Legacy parity spot-checks

Before each `DROP TABLE`, capture the affected page's rendered output
and diff key numbers against the post-migration version:
- Boxscore point totals for the two ported pages (2005, 2006).
- Player names/positions on each of the six ported history pages.
- Injury status text for a 2010–2019 record and a 2020+ record on the
  team roster/schedule page, to confirm the vocabulary conversion
  didn't corrupt display.

(Formatting may differ; the underlying data must match.)

## 4. Grep gate

- [ ] Zero remaining references to the bare `activations`, `players`,
      or `injuries` tables anywhere outside `symfony-app/migrations/`
      history (`revisedactivations`, `newplayers`, `newinjuries` are
      expected to remain — the rename-back is a deferred follow-up, not
      part of this PR).
- [ ] `App\Entity\Injury` and `App\Enum\InjuryStatusEnum` deleted with
      no remaining references.
- [ ] `scripts/database/transactionQueries.sql` updated or removed.

## 5. Schema audit deliverable

- [ ] `audit.md` exists in this spec directory with a keep/drop
      disposition for every table in `scripts/database/schema.sql` not
      already covered by groups 1–3.
- [ ] `tmp_players` and `tmp_scan` dropped (confirmed no code
      references before dropping).
- [ ] Anything ambiguous is listed in `audit.md`, not dropped.

## 6. No-regression

- [ ] Homepage widgets, quicklinks, team pages, standings, transactions,
      and stats pages unaffected (smoke pass) — none of them read the
      three dropped tables directly.
- [ ] `git status` clean after `git add -A` (Phase 3 gotcha: deletions
      swept up properly).

## 7. Merge checklist

- [ ] All of sections 1–6 pass.
- [ ] `specs/roadmap.md` updated (Phase 6 → Done, dated, with spec
      pointer; note the deferred canonical-rename follow-up).
- [ ] Code-review pass done (`/code-review`) and findings addressed.
- [ ] Migrations deploy in the sequence documented in `plan.md`
      (groups 1–3 independent, group 4 any time) during the current
      off-season window.
- [ ] PR description lists all three dropped tables, the row counts
      merged (injuries), and links to `audit.md` for the schema-wide
      keep/drop list.
