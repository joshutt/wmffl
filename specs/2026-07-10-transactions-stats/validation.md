# Validation — Transactions (Phase 4) + Stats (Phase 5)

The branch is mergeable when everything below passes. Validation level
agreed 2026-07-10: automated tests + fake-session manual E2E +
301-redirect verification + before/after DB state checks for every
write flow.

## 1. Automated tests

- `cd symfony-app && vendor/bin/phpunit tests/ --coverage-clover coverage.xml`
  passes (coverage in clover format per project convention — never
  `--coverage-text`).
- New functional tests exist for every new route (transactions history,
  waivers, protections show/form, IR, roster list/confirm, all stats
  pages, both CSV endpoints).
- Unit tests cover the ported computation/validation logic:
  - potential-vs-actual points service (power/weekbyweek/luck) —
    verify against known legacy outputs for at least one full season.
  - roster-move validation: every branch (roster full, 25-active limit,
    player unavailable, waiver-period rules, not logged in).
  - IR eligibility rules; protections deadline logic.
- Write endpoints reject: unauthenticated requests (401/redirect) and
  missing/invalid CSRF tokens.
- Root legacy suite (`vendor/bin/phpunit test/` from project root) shows
  no NEW failures (pre-existing include-path failures are known).

## 2. Manual E2E (fake-session recipe from Phases 1–3)

Run against the dev DB with `php -S` through `symfony-app/public/`
(remember the gotcha: `php -S` doesn't route `.php` paths through
index.php — test redirects with explicit requests).

Read pages, each renders with real data and no PHP errors:
- [ ] `/transactions` — current month; navigate to a past month/year;
      a month containing a trade renders the trade sentence correctly
- [ ] `/transactions/waivers` — order list; logged in shows "your
      priority"; last week's pickups table
- [ ] `/transactions/protections/show` — current + a past season
- [ ] `/stats` index, `/stats/leaders` (current + `?season=` past),
      `/stats/players` for every position incl. HC/K sort columns,
      `/stats/weekbyweek` both display modes, `/stats/power`,
      `/stats/luck`, `/stats/records`, `/stats/lastplayer`,
      `/stats/injuries` (both tabs, tablesorter works)
- [ ] CSV endpoints download with correct headers and row shape
- [ ] Main-nav "Stats" link and history-page `?season=` deep links work

Write flows, logged in as a test team:
- [ ] IR add then remove a player (JSON responses, page reflects state)
- [ ] Protections: select players, save, re-open form shows saved state;
      verify deadline-passed behavior
- [ ] Roster move: search in `/transactions/list`, add a free agent,
      drop a player; confirm the transaction appears in `/transactions`
- [ ] Each write attempted logged-out → blocked

## 3. DB state checks (before/after each write flow)

Capture before/after and diff — expected deltas only:

```sql
-- IR:        SELECT * FROM injuredreserve WHERE teamid=<T> ORDER BY dateon DESC LIMIT 5;
-- Protections: SELECT * FROM protections WHERE season=<S> AND teamid=<T>;
-- Roster:    SELECT * FROM roster WHERE teamid=<T> AND (dateoff IS NULL OR dateoff > NOW() - INTERVAL 1 DAY);
--            SELECT * FROM transactions ORDER BY date DESC LIMIT 10;
```

- Exactly the intended rows change; no writes occur from read pages or
  failed validations (re-run the SELECTs after a rejected attempt).
- Roster-limit rules verified in data: a team at 26 total cannot gain a
  27th row with `dateoff IS NULL`.

## 4. Legacy parity spot-checks

Before deleting each legacy page, capture its rendered output for the
current season and diff the key numbers against the new page:
- leaders table totals, power-ranking order, luck ratings, week-by-week
  totals for one team, waiver order list, protections list.
(Formatting may differ; numbers and ordering must match. Any deliberate
behavior change is documented in requirements.md.)

## 5. Redirects and no-regression

- [ ] Every deleted URL 301s to its replacement, incl. `.php` aliases
      (`/transactions/transactions.php`, `/stats/leaders.php`, …) and
      query-param carry-through (`/stats/leaders?season=2025`)
- [ ] Deleted-not-migrated pages (`info.php`, `standings.php`,
      `teamcompare.php`, `draftorder/`, `transactions/injury/`) return
      404 or redirect to a sensible parent — **`info.php` must no longer
      execute phpinfo()**
- [ ] Trades still fully work via LegacyBridge: `tradescreen.php`
      renders, its shared includes (`transmenu`, utils) survived the
      deletion sweep
- [ ] Homepage widgets, quicklinks, player profiles, team pages, history
      standings unaffected (smoke pass)
- [ ] `git status` clean after `git add -A` (Phase 3 gotcha: deletions
      swept up properly)

## 6. Merge checklist

- [ ] All of sections 1–5 pass
- [ ] `specs/roadmap.md` updated (Phases 4 & 5 → Done, dated, with spec
      pointer)
- [ ] Code-review pass done (`/code-review`) and findings addressed
- [ ] PR description lists every removed legacy URL and its redirect
