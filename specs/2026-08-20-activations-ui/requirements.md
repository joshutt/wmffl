# Requirements — Activations UI modernization (Phase 15)

## Scope

Port the weekly lineup-submission flow out of `football/activate/` into
Symfony, fix the SQL-injection hole in the submit handler, make both the
submit form and the "everyone's current lineup" view mobile-friendly, and
give commissioners a real admin tool for fixing a team's lineup.

In scope (the legacy files this phase owns):

| File | Lines | Fate |
|---|---|---|
| `submitactivations.php` | 392 | ported → `ActivationController::submit` |
| `processActivations.php` | 125 | ported → `ActivationController::save` |
| `currentactivations.php` | 122 | ported → `ActivationController::index` partial |
| `activations.php` | 21 | ported → `ActivationController::index` |
| `activationButtons.php` | 6 | ported → Twig partial / nav |
| `index.php` | 1 | deleted (includes `activations.php`) |
| `info.php` | 10 | deleted (phpinfo dump, dead) |
| `submitthanks.php` | 27 | deleted (orphaned) |
| `update2.inc` | 1 | deleted (stray text file: "Sep 20 - 11:50 PM") |

Explicitly **not** in scope — these stay on the LegacyBridge until Phase 17
(Boxscores redesign):

- `currentscore.php` (458 lines) — live/historical box-score rendering
- `scoreFunctions.php` (113 lines)
- `base/scoring.php` and friends

Also out of scope: the login/auth system (Phase 18), any change to how
`playerscores` are computed (Phase 17), and historical lineup repair for
past seasons beyond what the admin override tool naturally allows.

The `gameplan` table, its rows, and its Doctrine mappings all stay — see
Decision 8; gameplan history is deliberately preserved, and this phase
only stops the activations pages from writing it. Its two remaining
readers live outside this phase's file list
(`football/activate/scoreFunctions.php:64-65` renders the `GP+`/`GP-`
markers on box scores, Phase 17; `scripts/livescore/updatescores.php:23-24`
joins it, Phase 19), so the table cannot be dropped until those are
retired.

## Context — what the legacy code actually does

### Submit page (`submitactivations.php`)

Four queries drive the page, all built with interpolated (but
internally-sourced) values:

1. **Main roster JOIN** — `players` × `roster` (`dateoff is null`) ×
   `nflrosters` × `activations` (this season/week/team) × `nflgames` ×
   `injuries` × `ir`, ordered by `pos`, `lastname`. Produces name, pos,
   NFL team, whether the player is already activated, kickoff time,
   home/road team, injury status/details, and current-IR flag.
2. **`noActivateSql`** — players who joined this team after week 14's
   `weekmap.ActivationDue`; they are force-locked into Reserves for the
   rest of the season.
3. **`actingHCsql`** — free-agent (unrostered) head coaches whose NFL game
   kicks off more than 30 minutes from now and who are either unactivated
   or already activated by this team. Only consulted when the team's own
   rostered HC has no game this week (`deadLine == 0 && nfl != null`).
4. **`opponentRoster`** — this week's opponent's roster. **Its results are
   computed and then discarded** — the loop assigns to a local `$player`
   that is never read. Dead code; the opponent roster is never displayed.

Rendering: a two-section table (Starters / Reserves) with a checkbox per
player named `<POS>[]`, a lock icon (`/images/lock-clipart2.gif`) plus a
hidden input in place of the checkbox for locked starters, and a week
`<select>`.

### Locking

- **Per player**, submit page: locked when `now > kickoff - 5 minutes`
  (proposal 2023.2 changed this from 2 hours; the old
  `$realTime = kickoff - 2*60*60` variable is still computed and unused).
- **All-lock**: once `now` passes the *latest* kickoff among the team's
  roster, everything locks.
- **No game / bye** (`kickoff` empty): `deadLine = 0`, never locks.
- **Post-week-14 acquisitions**: force-locked as reserves regardless of
  kickoff (`noActivateSql`).
- **Current-activations view** uses a *different* threshold: a player is
  marked with a `*` unless `now < kickoff - 30 minutes`. This 30-minute
  value is inconsistent with the submit page's 5 minutes — see Decisions.

### Save handler (`processActivations.php`)

Reads `HC/QB/RB/WR/TE/K/OL/DL/LB/DB` arrays plus `actHC`/`actHCid` from
`$_REQUEST`, validates position counts, and on success deletes the team's
rows for that season/week and re-inserts. On validation failure it
`include`s `submitactivations.php` so the form re-renders with
`$activeMessage` and the user's checkbox state intact.

**The SQL-injection surface**: the insert is built as
`"($season, $week, $teamnum, '$key', $item)"` where `$item` comes straight
from `$_REQUEST` with no validation, casting, or binding — the roadmap's
step 3, same fix pattern as Phase 3's `teams/compare`.

There is also no ownership check on the inserted player ids: a crafted
POST can activate a player the team does not roster.

### Position limits (currently inline `sizeof()` checks)

1 HC · 1 QB · 1–2 RB · 2–3 WR · 1–2 TE with RB+WR+TE = 5 · 1 K · 1 OL ·
2 DL · 2 LB · 2 DB.

### Broken/dead things found

- The week `<select>`'s `onChange="swapActivations(this)"` does nothing
  today. `submitactivations.php` declares
  `$javascriptList = ['/base/js/activations.js']` and
  `$cssList = ['/base/css/activate.css']`, and both files do exist
  (served from `symfony-app/public/base/`) — but `swapActivations()`
  AJAXes to `weekSubAct.php?week=N`, and **`weekSubAct.php` does not
  exist anywhere in the repo**. The request 404s, the handler's
  `status == 200` guard fails, and `changeTable()` never runs, so
  switching weeks only takes effect on submit.
- Both asset files are consumed *only* by `submitactivations.php` and go
  with it: `activations.js` is the dead swapper above (plus a hand-rolled
  `XMLHttpRequest` helper that `eval()`s its callback name), and
  `activate.css` is 8 lines — `#subAct`, styling the table this phase
  replaces, and `.gameplanbox`, a GP remnant (Decision 8).
- `getInjuryLine()` is defined at the top of `submitactivations.php` and
  never called (the render uses `getPQDOLine()` from `utils/injuryUtils.php`).
- `$format`/`$realTime` locals: unused.
- `submitthanks.php`: orphaned — `processActivations.php` redirects to
  `activations.php`, never here.
- **Gameplan / GP remnants** (see Decision 8) — more of them than the
  roadmap's summary implied:
  - `processActivations.php:21-22` — live (not commented) reads of
    `myGP`/`oppGP` from `$_REQUEST`, feeding nothing.
  - `processActivations.php:79, 100-122` — the commented-out
    `DELETE FROM gameplan` / `INSERT INTO gameplan` block and its
    `$useGp` flag. Note the insert has the *same* unbound-interpolation
    SQL-injection shape as the activations insert; it is being deleted,
    not fixed.
  - `currentactivations.php:42` — `$gpLine = array();`, declared and
    never read.
  - `symfony-app/public/base/css/activate.css:1` — a `.gameplanbox` rule,
    matching no markup left in the app.

## Decisions (confirmed with Josh, 2026-08-20)

1. **Lineup rules live in the `seasons` table as per-season JSON.**
   A new `lineup_rules` JSON column alongside `scoring_rules`, fronted by
   a `LineupRuleRegistry` (defaults + metadata) and a `LineupRules` value
   object, exactly mirroring the `ScoringRuleRegistry`/`ScoringRules`
   pattern from the Season Rules foundation. The registry is the single
   canonical definition consumed by the submit form, the server-side
   validator, the JS counters, and the admin override tool — no duplicated
   count checks anywhere. Editable per season via `/admin/seasons`.
   Migration backfills every existing season row with the current defaults
   (`verified` semantics unchanged — Josh corrects historical seasons as
   their actual rules are recreated).
2. **A commissioner-only lineup override page is in scope.** `Become Team`
   (`AdminBecomeController`) cannot fix a lineup after kickoff because the
   member form's locks still apply. The override tool lets a commissioner
   set any team's lineup for any season/week, bypassing the per-player
   kickoff lock and the post-week-14 restriction, with the position-count
   rules still enforced (an admin fixing a lineup should not be able to
   create an illegal one by accident — see the override in Decision 5).
3. **Server-side validation stays authoritative; JS counters are
   progressive enhancement.** The form works with JS off exactly as it does
   today (submit → re-render with the error list and the user's selections
   preserved). Small vanilla-JS live counters ("WR 2/3") flag violations
   before the round-trip and disable the submit button while the lineup is
   illegal, but never gate the server check. No JS framework, per
   `specs/tech-stack.md`.
4. **The current-activations view pairs teams by matchup.** The legacy
   query already orders by `s.gameid`; the new view groups each scheduled
   game's two teams into one matchup block (side-by-side on desktop,
   stacked on mobile) instead of today's arbitrary odd/even 2-column
   pairing. Cards reflow to a single column on narrow viewports.
5. **Lock thresholds are unified at 5 minutes.** The current-activations
   view's 30-minute `*` marker is brought in line with the submit page's
   5-minutes-before-kickoff lock (proposal 2023.2), so the two pages stop
   disagreeing about whether a player is locked. The acting-HC query's
   separate 30-minute horizon is *kept* as-is — it is a different rule
   (how much lead time a free-agent HC pickup needs), not the same
   threshold, and changing it is not this phase's call.
6. **The admin override may create an out-of-position-limit lineup only
   deliberately.** The override form enforces the same counts by default
   but offers an explicit "save anyway (illegal lineup)" confirmation, so
   a commissioner can reproduce a historically-illegal lineup for the
   record (the league fines illegal lineups rather than rejecting them —
   see `seasons.illegal_activation_fine`).
7. **Player-ownership is validated on save.** Every submitted playerid must
   be on the submitting team's current roster (or be a legal acting-HC
   free agent), rejected server-side with a friendly error rather than
   silently inserted. This closes the second half of the
   `processActivations.php` hole alongside parameter binding.

8. **Gameplan/GP leaves the activations pages, but the data and its
   mappings are preserved.** Gameplan is a retired feature whose
   historical rows are still worth keeping and reading, so this is a
   *disconnection*, not a deletion (revised 2026-08-20, superseding an
   earlier draft of this decision that had the entities being removed).

   Out of the activations code: nothing named `gameplan`, `myGP`,
   `oppGP`, `$gpLine`, `side='Me'`/`'Them'`, or `GP+`/`GP-` survives into
   the ported controller, service, repository, templates, or JS — not
   even commented out. Concretely, the `myGP`/`oppGP` request reads, the
   commented `gameplan` DELETE/INSERT block with its `$useGp` flag, the
   unused `$gpLine` array, and the `.gameplanbox` CSS rule all disappear
   along with the files that hold them, rather than being translated. The
   new `ActivationRepository` joins `gameplan` in no query, and
   `ActivationService::save()` writes to no table but `activations`.
   After this phase nothing can *write* a gameplan row.

   Kept, deliberately and permanently as far as this phase is concerned:

   - the `gameplan` table and all its rows (~1,300, per its
     `AUTO_INCREMENT`) — historical record;
   - `App\Entity\Gameplan` and `App\Enum\GameplanSideEnum` — the
     Doctrine mappings stay so the history remains queryable from
     Symfony, even though nothing references them today. They are not
     dead code to be swept up; they are the read path for data whose
     writers are being retired. A later phase that surfaces gameplan
     history (a box-score archive, a season retrospective) will want
     them already in place.
   - `football/activate/scoreFunctions.php:64-65, 81-84` (Phase 17) and
     `scripts/livescore/updatescores.php:23-24` (Phase 19) — the two
     remaining readers, each owned by its own phase.

   Any future proposal to drop the table or the entities is a separate,
   explicitly-approved decision — not a cleanup that rides along with a
   migration phase.

## Non-goals / risks carried forward

- The `activations` table keeps its current shape (composite PK
  `season, week, teamid, playerid` + `pos`); no schema change beyond the
  `seasons.lineup_rules` column.
- Backfilling `lineup_rules` seeds *current* limits for all seasons
  1992–2026. Older seasons almost certainly had different limits; those
  are Josh's to correct via `/admin/seasons`, same as scoring rules.
- `currentscore.php`'s own copy of activation-reading logic stays on the
  LegacyBridge this phase and will diverge slightly (it keeps its 30-minute
  marker) until Phase 17 retires it.
- Because the box score keeps rendering `GP+`/`GP-` from the surviving
  `gameplan` rows, GP remains *visible* in the app after this phase even
  though it is no longer writable from anywhere. That is expected and
  desirable — the historical markers should keep rendering. Phase 17
  inherits the question of how the redesigned box score presents them;
  it should not assume they can simply be dropped.
