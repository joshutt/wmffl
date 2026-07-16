# Phase 9a — History (non-season-specific): Requirements

## Scope

Migrate the top-level pages of `football/history/` to Symfony: the
history hub, past champions, past #1 draft picks, all-time team W/L
records, player single-season/single-week records, and the dynamic
team-money ledger. None of the per-season content
(`{year}Season.php` / `{year}Season/`) is in scope — that's Phase 9b.
Boxscores (`2005Season/boxscores.php`, `2006Season/boxscores.php`)
are Phase 10's.

Branch `history-9a`, one PR, one commit per task group in `plan.md`.

Decisions made 2026-07-16 (with Josh, via AskUserQuestion):

1. **`teammoney.php` (dynamic) belongs to 9a** — it's already a single
   `?season=`-parameterized route, not a per-season file explosion.
   The frozen `{year}Season/teammoney.php` / `money.php` snapshots
   (hand-written HTML, different report) stay in 9b. Distinct from
   `AdminMoneyController`, which covers only the commissioner *edit*
   flow — this is the public *view*.
2. **`paststreaks.php` is dropped outright** — hidden from the nav
   today (`d-none`), data frozen at 2015, nothing else references it.
   No redirect.
3. **Past champions becomes data-driven, backed by `titles`**:
   division titles read straight from `titles` (type=Division);
   the League Champions and Toilet Bowl tables are derived from
   `schedule` (find the championship / toilet-bowl game per season,
   winner + scores from the row). The admin season-flags tool gains a
   sync step that creates/updates `titles` rows from the checked
   flags, so future seasons never need a code change.
4. **HTML-only output** — no ajax/csv/json variants; the legacy pages
   don't have them and nothing embeds these tables.
5. **Extend `TeamRepository` / `PlayerRepository`** with the new
   queries rather than adding a history-only repository.

Standard hardening applies (as in Phases 1–8): bound parameters
everywhere (the legacy record pages `sprintf` the position into SQL —
constant-source today, but bind anyway), CSRF on any new POST.

## Legacy surface (what we're replacing)

| Legacy file | New route | Nature |
|---|---|---|
| `index.php` | `/history` | Nav hub. Its 35 season links keep pointing at the still-legacy per-season pages until 9b |
| `pastchamps.php` | `/history/pastchamps` | Division champions (3 divisions, era-aware names), League Champions table (year/winner/score/loser/score/MVP), Toilet Bowl games table |
| `pastdrafts.php` | `/history/pastdrafts` | #1 overall pick per season + computed summaries (picks per team, picks per position) |
| `alltimerecords.php` | `/history/alltimerecords` | Team W/L records: overall / regular season / post-season / playoffs / championship game / toilet bowl splits |
| `recordseason.php` | `/history/recordseason` | Per-position all-time single-season top-10 (+ties) leaderboards |
| `recordsweek.php` | `/history/recordsweek` | Same, single-week, with team-at-the-time columns |
| `teammoney.php` + `common/moneyUtil.php` | `/history/teammoney` | Per-team ledger: previous balance, paid, late fees, illegal-lineup and bye-week charges, extra transactions, win/playoff payouts; `?season=`, defaults current |
| `paststreaks.php` | — | dropped, no redirect |

## Data model findings (verified against the dev DB, 2026-07-16)

- **`titles`** (`App\Entity\Title`, composite key season+type+team,
  `TitleTypeEnum` League/Division/Toilet): League 32 rows 1992–2023,
  Division 77 rows 1992–2023, Toilet 25 rows 1999–2023. **Stops at
  2023** — 2024/2025 exist only as hardcoded HTML in the legacy page.
- **`season_flags`** (`App\Entity\SeasonFlag`): covers 2024–2026 with
  `champion` / `division_winner` / `finalist` / `playoff_team`
  booleans, already maintained via `/admin/money/updateFlags`. The new
  sync (decision 3) maps champion→League, division_winner→Division;
  running it for 2024 and 2025 backfills the `titles` gap. There is no
  toilet flag and none is needed — toilet-bowl results are derived
  from `schedule`.
- **`division`** (`App\Entity\Division`): era-aware names keyed
  divisionid + startYear/endYear (Blue 1993–2002 → Burgundy 2003–,
  Orange 1993–2002 → Gold 2003–, White 2010–; a 1992 "League" row
  predates divisions). `teamnames.divisionId` links a team-season to
  its division — this drives the grouped division-champions rendering,
  including the mid-table rename headers the legacy page hardcodes.
- **`schedule`**: `championship=1` rows back to 1992; toilet bowl =
  `postseason=1 AND playoffs=0 AND championship=0` (earliest 1999).
  Winner/loser + scores from `TeamA/TeamB/scorea/scoreb`, names via
  season-correct `teamnames`. Verified: 2023 toilet game (Amish
  Electricians 51–0) matches `titles` type=Toilet 2023.
- **Championship MVP names have no DB source** (`ballot` is
  issue-voting; `MvpScoringService` computes ranks on demand, it
  doesn't store awards). The MVP column stays a static season→name map
  carried in the controller/template, documented as a future
  data-driven candidate — same convention as
  `PlayerRecordsService`'s hardcoded thresholds.
- **`draftpicks`** covers 2005+ (Round=1, Pick=1 → the #1 overall,
  `playerid` joinable to `players`). 1992–2004 picks stay a static
  array (entries like 1994's "Seattle QB" don't map to player rows).
  The legacy page is frozen at 2023; deriving 2005+ from the DB adds
  2024/2025 automatically and keeps future drafts free. Player's NFL
  team *at draft time* via `nflrosters` as-of the draft date (same
  as-of join `recordsweek.php` already uses).
- **`recordseason`/`recordsweek` supplemental pre-2003 records**
  (`$qbList` etc., name/season/pts) are the same historical dataset
  `PlayerRecordsService::SEASON_THRESHOLDS`/`GAME_THRESHOLDS` carries
  as bare thresholds. Port them as constants next to the thresholds so
  the two copies at least live in one file; making them data-driven is
  out of scope (matches that service's existing "ported as-is" note).
- **`teammoney` queries** (`moneyUtil.php`): `getExtraCharges` (three
  UNIONed illegal-lineup checks + bye-week count + remaining
  transaction points), `getWins`, `getSeasonFlags`, `getLastUpdate`.
  All already parameterized (`:season`) and already running through
  the shared Doctrine EntityManager — a clean lift into a
  `TeamMoneyService` on Symfony's `Connection`. Reuses
  `App\Entity\Paid` + `App\Entity\SeasonFlag` (the same entities
  `AdminMoneyController` edits) and `SeasonWeekService` for the
  current-season default and the season-rollover quirk (before
  August, week 0 of the current season shows last season's ledger).

## Admin tooling (mission requirement)

- **Titles sync on season-flags save**: extend the season-flags flow
  (`AdminMoneyController::processFlags`) so saving a season's flags
  creates/updates that season's `titles` rows — champion→League,
  division_winner→Division (division resolved via
  `teamnames.divisionId`). Unchecking a flag removes the matching
  title row for that season, keeping the two in sync. Earlier seasons
  (≤2023) are untouched unless their flags page is deliberately saved.
- Team money view needs no new admin tool — `AdminMoneyController`
  already covers payments and flags.
- Past drafts needs no admin tool — 2005+ flows from `draftpicks`,
  which the draft tooling maintains.

## Out of scope

- Per-season pages (Phase 9b), boxscores (Phase 10)
- Any ajax/csv/json output formats (decision 4)
- Data-driving the MVP map, the pre-2005 draft picks, or the pre-2003
  player records (documented candidates, not this phase)
- Renaming/replacing `PlayerRecordsService`'s threshold constants
