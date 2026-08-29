# Requirements — Phase 16b: Draft Results under Transactions

## Summary

Replace the 20 legacy draft-board files (`football/history/common/draftresults.php`
plus one thin per-year wrapper for each of 2007–2025) with a single Symfony
route, `/transactions/draftresults/{year?}`, backed by one repository/service
that drives every season the `draftpicks` table holds. Add a minimal
commissioner-only admin page for fixing a pick's selection.

This is the carve-out documented in `specs/roadmap.md` under "Phase 16b —
Draft results under Transactions", split from Phase 16 at Josh's request:
draft results belong under Transactions, not History, and the year belongs
on the path rather than in a per-season hub directory.

## Scope

In scope:
- A `DraftResultsService` + additions to the existing `DraftPickRepository`
  porting the three queries from `common/draftresults.php`: the pick list
  (with its round/pick/team/pos/NFL filters), the distinct-team lookup and
  the distinct-NFL-team lookup that populate the filter dropdowns.
- `DraftResultsController` on `/transactions/draftresults/{year?}`
  (`year` optional int, `requirements: ['year' => '\d+']`).
- A new Twig template rendering the full draft board with a GET filter
  form and prev/next year links.
- A "Draft Results" button on the shared transactions button bar
  (`templates/transactions/_transmenu.html.twig`).
- Updating all 19 legacy link sites (11 flat `history/{year}Season.php`
  hub pages for 2007–2017, 8 `history/{year}Season/index.php` nav lists
  for 2018–2025) to point at the new route.
- Deleting `common/draftresults.php` and all 19 per-year wrappers, with
  301 redirects from `/history/{year}Season/draftresults[.php]`.
- A minimal admin page, `/admin/draftresults/{year}`: set or clear the
  player on any pick row for a season, via player search.
- Traded-pick provenance on the board: a "from <original franchise>" note
  on any pick whose `orgTeam` differs from `teamid`. New — legacy never
  showed it.
- A future-season access cut-off: no draft later than the current season
  is reachable, on either the public or the admin page.

Out of scope:
- Phase 16 itself (the per-season history hub content). Phase 16's generic
  hub template, when built, links out to this route.
- `draftorder` (the word-game page — already 301'd to the transactions hub
  by `LegacyTransactionRedirectController`) and the live draft clock
  (`football/services/clock.class.php`).
- Pick *ownership* administration: editing `teamid`/`orgTeam`, adding or
  deleting pick rows, seeding a future season's skeleton. Josh chose the
  minimal admin scope; ownership editing belongs with future live-draft
  tooling.
- Any data backfill. `draftpicks` already holds everything the new page
  needs.

## Data findings (verified against the dev DB, 2026-08-29)

- `draftpicks` has rows for **2005–2029**. 2005 is 120 rows with
  `playerid` entirely NULL (skeleton only) and 2027–2029 are future
  seasons the cut-off hides (decision 6), so the page's reachable range
  today is **2005–2026** — with 2006 the first season holding actual
  selections, one year earlier than any legacy page existed for.
- Draft shape varies by era: 2006–2009 is 12 rounds × 10 picks (120 rows),
  2010–2012 is 12 × 12 (144), 2013–2029 is 16 × 12 (192). The legacy
  filter form hardcoded Round 1–12 and Pick 1–12 — already wrong for every
  16-round season since 2013. Filter options must be derived from the
  season's data.
- Partial and empty drafts are normal, not edge cases:
  - **2006**: 37 of 120 picks have a `playerid` (an incomplete historical
    record).
  - **2026** (current): 192 rows with pick order set, 20 selections made —
    a draft in progress.
  - **2027–2029**: full round skeletons with `pick` NULL and no
    selections — draft order not yet set. **Not reachable** (decision 6);
    recorded here because the rows exist and the queries must not trip
    over them.
- Within the reachable range (2006–2026), `draftpicks.pick` is never NULL
  — every one of those seasons has its pick order set. NULL picks exist
  only in the unreachable future seasons.
- `players.pos` among drafted picks includes `HC` (head coach) and an
  empty string alongside the expected QB/RB/WR/TE/K/OL/DL/LB/DB. The
  legacy Pos dropdown also had a live bug — `<option name="OL">` instead
  of `value="OL"` for OL/DL/LB/DB — so those four filters never worked.
- `weekmap` has a week 1 row for every season 2006–2026, which the new
  NFL-team as-of join depends on.
- `draftpicks.orgTeam` (the franchise that originally held the pick) is
  **never NULL** for any season — every row is safe to compare against
  `teamid` without a null guard. Traded picks are a small minority per
  season: 26 in 2007 (the high), 2 in 2019 (the low), 5–12 in a typical
  year, and 1 so far in 2026.
- **`teamnames` stops at 2026** — 12 rows per season through the current
  season, nothing beyond. With the future-season cut-off in place every
  reachable season has its rows, so this is no longer a correctness
  problem. It stays a rollover hazard: when 2027 becomes the current
  season it becomes reachable immediately, and if its `draftpicks`
  skeleton is seeded before its `teamnames` rows are, legacy's
  `JOIN teamnames ON teamid AND season` would render the board as **zero
  rows**. The `team` table carries one current name per franchise (13
  rows, including the defunct `Fighting Squirrels (1996)`) and is the
  fallback that keeps that from happening. `weekmap` has the same shape
  and the same rollover hazard for the NFL as-of date (decision 2).

## Decisions from user Q&A (2026-08-29)

1. **Empty picks — full board, blank selection.** Render every pick row
   the season has, including ones with no player, showing an em dash in
   the Selection/Pos/NFL cells. This makes 2006's gaps (37 of 120) and the
   in-progress 2026 draft read correctly, and keeps the current season's
   board legible before its draft runs. This is a deliberate change from
   legacy, whose `JOIN players` silently dropped such rows. Rows with a
   NULL `pick` sort last within their round and show a blank Pick cell —
   no reachable season has one today, so that rule only matters at
   rollover (see decision 6 and the `teamnames` note above).
2. **NFL as-of date — week 1 activation due date.** Each legacy wrapper
   hardcoded a `$dateSet` (roughly draft day, e.g. `'2020-08-29'`) used to
   resolve each player's NFL team from `nflrosters`; those files are being
   deleted. Replace it with the season's `weekmap` week-1 `ActivationDue`,
   the same as-of pattern `DraftPickRepository::getNumberOnePicks()`
   already uses. No new column, no backfill, works for every season.
   Known consequence: week 1 is ~1–2 weeks after draft day, so a player
   cut or traded in that window shows his week-1 NFL team rather than his
   draft-day one. Accepted. When a season has no `weekmap` week-1 row —
   which within the reachable range can only happen to a newly current
   season before its `weekmap` rows are seeded — fall back to "today"
   (`dateoff IS NULL` semantics) rather than dropping the column.
3. **Year navigation — prev/next links.** `« 2024 | 2025 Draft Results |
   2026 »` above the board; the filter form keeps the legacy five
   controls (Round, Pick, Team, Pos, NFL) with no Year dropdown. Prev/next
   are bounded by the reachable range — the earliest season with
   `draftpicks` rows, and the **current season** as the upper bound
   (decision 6) — and the edge link is omitted (not rendered disabled) at
   each end, so the current season's board shows no "next" link.
5. **Traded-pick provenance — in scope (added 2026-08-29).** Any pick
   whose `orgTeam` differs from `teamid` shows a "from <original
   franchise>" note beneath the owning franchise in the Franchise cell,
   e.g. 2007 round 1 pick 1: `Gallic Warriors` / `from Pretend I'm Not
   Here`. Franchise names are the season-specific `teamnames.name` on both
   sides, so an old board names each franchise as it was known that year.
   The **Team filter keeps legacy semantics — it matches the owning
   franchise only**, not the original; filtering by a team answers "what
   did this team draft", which is what the legacy filter meant.

4. **Admin — minimal edit.** New commissioner-only
   `/admin/draftresults/{year}`: for any pick row in the season, set the
   player (via the existing player search) or clear it. No pick
   reordering, no ownership editing, no row add/delete. Satisfies the
   mission's "admins can administer it without raw DB access" bar for
   what this page displays.

6. **No access to future drafts (added 2026-08-29).** A season later
   than the current season is not reachable at all: `/transactions/
   draftresults/2027` 404s today rather than rendering a skeleton board,
   the prev/next navigation stops at the current season, and the admin
   page applies the same cut-off. The bound is the current season from
   `SeasonWeekService`, not a hardcoded year, so it advances on its own at
   each rollover. The current season itself stays reachable while its
   draft is in progress or not yet begun — 2026 is reachable today with
   20 of 192 picks made, and would still be reachable at 0 of 192.

## Default-year resolution

- `/transactions/draftresults` (no year): the current season if it has any
  **drafted** picks (non-NULL `playerid`), otherwise the most recent
  season that does. Today that resolves to 2026 (20 selections in); in a
  quiet offseason before any pick is made, it resolves back to 2025.
  Never resolves to a skeleton-only season.
- `/transactions/draftresults/{year}` (explicit, including 2006): renders
  that year directly with no fallback, provided it is in range.
- **In range** means: the year has `draftpicks` rows *and* is no later
  than the current season. Everything else 404s — a year with no rows at
  all (2004, 2050) and any future season (2027–2029 today), which are
  indistinguishable to a visitor, as intended.
- 2005 stays reachable: it is a past season with rows, so it renders as a
  skeleton board (120 rows, no selections). Only *future* seasons are cut
  off, not empty past ones.

## Filtering

- Filters submit via **GET query params on the same route**
  (`?round=2&team=5`), replacing legacy's POST-to-self, so any filtered
  view is a shareable, bookmarkable URL. Empty/absent params and the
  literal `ALL` both mean "no filter."
- All five filter values are **bound query parameters**, never
  interpolated into SQL — legacy dropped `$_REQUEST` values straight into
  the WHERE clause. Same fix pattern as Phase 3's `teams/compare` and
  Phase 15's activations submit.
- Dropdown options are derived from the season being viewed: rounds and
  picks from that season's actual `draftpicks` range, teams from
  `teamnames` for that season, positions from the positions actually
  drafted, NFL teams from `nflrosters` as of the same as-of date. This
  fixes both legacy dropdown bugs (hardcoded 1–12, broken OL/DL/LB/DB
  options) for free.
- Filtering applies to the full board including undrafted rows, except
  that a Pos or NFL filter necessarily excludes rows with no player (they
  have no position or NFL team to match).

## Conventions to follow

- Repository/service style: DBAL `Connection` with named parameters on
  the existing `DraftPickRepository`, matching `getNumberOnePicks()` and
  `ScheduleRepository`.
- Controller style: thin controller, service builds the view model —
  match `ScheduleController` (Phase 16a).
- Template style: extends `base.html.twig`, `.cat` section header, plain
  Bootstrap table, no bespoke CSS file (the legacy
  `/base/css/draftresults.css` it referenced is empty on disk anyway).
  Buttons use `btn-wmffl` inside a `text-center` wrapper.
- Admin controller extends `AbstractAdminController`
  (`requireCommissioner` + `assertCsrfToken` on every mutating action),
  and reuses `PlayerRepository::searchPlayers()` for the player picker —
  same shape as `AdminPlayerController`.
- Redirect controller: a small dedicated controller in the
  `Legacy*RedirectController` family, one regex-driven route covering all
  affected years (Phase 16a precedent), 301
  (`Response::HTTP_MOVED_PERMANENTLY`).

## Branch

Work happens on `phase16b-draftresults` (created 2026-08-29 off `main` at
`cc1312a`, which already carries the Phase 16b roadmap entry).
