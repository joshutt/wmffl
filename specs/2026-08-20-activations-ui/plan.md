# Plan — Activations UI modernization (Phase 15)

Branch: `phase15-activations-ui`

## 1. Lineup rules as season data

- `symfony-app/src/Service/LineupRuleRegistry.php` — the single canonical
  definition, mirroring `ScoringRuleRegistry`'s shape: per-position
  `['min' => n, 'max' => n, 'label' => ...]` entries for
  HC/QB/RB/WR/TE/K/OL/DL/LB/DB, plus the cross-position constraint
  (`flex_group` = RB+WR+TE, `flex_total` = 5). Ships `defaults()` and
  `definitions()` like the scoring registry.
- `symfony-app/src/Model/LineupRules.php` — value object built by
  `LineupRules::fromArray($overrides)` (season overrides merged over
  registry defaults, unknown keys dropped via `array_intersect_key`,
  same as `ScoringRules::fromArray`). Exposes `min(string $pos)`,
  `max(string $pos)`, `flexTotal()`, and `validate(array $countsByPos):
  array` returning the ordered human-readable error strings — the *only*
  place lineup legality is decided.
- `Season` entity: add `#[ORM\Column(name: 'lineup_rules', type: 'json')]
  private array $lineupRules = []` + accessors.
- `SeasonRuleService::getLineupRules(int $season): LineupRules`, cached
  the same way as `getScoringRules()`.
- Migration `Version<timestamp>.php`: `ALTER TABLE seasons ADD COLUMN
  lineup_rules JSON NOT NULL` and seed every existing row with the
  registry defaults; `down()` drops the column. Update
  `scripts/database/schema.sql` to match.
- `/admin/seasons` edit form: a "Lineup" fieldset generated from the
  registry (same generated-per-group approach `AdminSeasonController`
  already uses for scoring), saved through the same POST handler.

## 2. Repository: reads

`symfony-app/src/Repository/ActivationRepository.php`, DBAL-based with
bound parameters (following `TeamRepository`'s constructor + `Connection`
style, including its `INJURY_SHORT` map convention for status labels):

- `getSubmitRoster(int $season, int $week, int $teamId): array` — the main
  roster JOIN from `submitactivations.php` (players × roster × nflrosters
  × activations × nflgames × injuries × ir), returning per-player name,
  pos, NFL team, opponent string, kickoff, already-activated flag,
  injury label/detail, IR flag.
- `getPostDeadlineAcquisitions(int $season, int $teamId): int[]` —
  `noActivateSql`; playerids force-locked to reserve.
- `getActingHeadCoachOptions(int $season, int $week, int $teamId): array` —
  `actingHCsql`, keeping its own 30-minute kickoff horizon (per
  requirements Decision 5).
- `getWeekOptions(int $season): array` — `weekmap` rows with
  `EndDate > now()` for the week picker.
- `getCurrentActivations(int $season, int $week): array` — the
  `currentactivations.php` query, ordered by `gameid`, shaped for
  matchup pairing.
- `getRosteredPlayerIds(int $season, int $teamId): int[]` — for the
  ownership check on save.
- Drop the legacy `opponentRoster` query entirely: its results were never
  rendered (see requirements). Do not port dead code.
- No query added by this phase joins or reads `gameplan` (requirements
  Decision 8). The table keeps its existing readers elsewhere; the
  activations code simply is not one of them.

## 3. Service: lock semantics + the write path

`symfony-app/src/Service/ActivationService.php`:

- `lockStateFor(array $player, \DateTimeImmutable $now): bool` — locked
  when `now > kickoff - 5 minutes`; never locked when the player has no
  game (bye/no NFL team); plus the team-wide all-lock once `now` passes
  the latest kickoff on the roster.
- `buildSubmitView(int $season, int $week, int $teamId, ?array $submitted)` —
  splits starters/reserves (respecting a rejected POST's selections so
  the re-render keeps the user's checkboxes, exactly as the legacy
  include-back does), applies locks, marks post-week-14 acquisitions,
  decides whether the acting-HC picker is needed.
- `save(int $season, int $week, int $teamId, array $selectionsByPos,
  ?int $actingHcId, bool $allowIllegal = false): array` —
  1. normalize every incoming id to `int`
  2. validate counts through `LineupRules::validate()` (skipped only when
     `$allowIllegal`, the admin override path)
  3. validate ownership: every id ∈ `getRosteredPlayerIds()`, except a
     legal acting-HC id ∈ `getActingHeadCoachOptions()`
  4. reject any player whose lock is engaged from changing state (member
     path only; the admin path bypasses locks)
  5. inside one transaction: `DELETE FROM activations WHERE season=:s AND
     week=:w AND teamid=:t` then a single **parameter-bound** multi-row
     insert
  - Returns the error list; empty means saved. **No string interpolation
    of request values anywhere** — this is the roadmap's step 3 fix.
  - `activations` is the only table written. The legacy `gameplan`
    delete/insert (commented out, and carrying the same injection shape
    as the activations insert) is not ported — requirements Decision 8.

## 4. Member controller + routes

`symfony-app/src/Controller/ActivationController.php`:

- `GET /activations` (`activations`) — current-activations view for the
  current season/week, `?season=`/`?week=` overrides (cast to int).
- `GET /activations/submit` (`activations_submit`) — the form; requires
  login (`AuthenticationService::isLoggedIn()`, rendering the existing
  `_login_required.html.twig` otherwise, matching `RosterMoveController`);
  team comes from `AuthenticationService::getTeamNumber()`, never from
  the request. `?week=` switches the week (a real GET round-trip —
  replaces the dead `swapActivations()` JS, see requirements).
- `POST /activations/submit` (`activations_save`) — CSRF-checked; on
  validation failure re-renders the form with errors + submitted
  selections (HTTP 422); on success flashes and redirects to
  `activations`.

## 5. Admin lineup override

`symfony-app/src/Controller/Admin/AdminActivationController.php`, route
prefix `/admin/activations`, extends `AbstractAdminController` with
`requireCommissioner()` + `assertCsrfToken()` on every POST:

- `GET /admin/activations` — team + season + week picker.
- `GET /admin/activations/{teamId}/{season}/{week}` — the same lineup
  form, but every player selectable regardless of lock state and with
  post-week-14 acquisitions available.
- `POST` same path — calls `ActivationService::save()` with locks
  bypassed; position rules still enforced unless the explicit
  "save anyway (illegal lineup)" checkbox is set (requirements
  Decision 6), which routes through `$allowIllegal = true`.
- Add an "Activations" entry to `templates/admin/base.html.twig`'s nav,
  following the existing `_route starts with 'admin_activations'`
  active-state pattern.

## 6. Templates

`symfony-app/templates/activations/`:

- `index.html.twig` — matchup-paired cards (requirements Decision 4):
  one block per `schedule` game, the two teams side-by-side on desktop
  (Bootstrap grid, `col-md-6`) collapsing to stacked full-width on
  mobile; per-player row shows pos (with the `*` lock marker at the
  unified 5-minute threshold), name, opponent, injury label.
- `submit.html.twig` — Starters/Reserves sections; on narrow viewports
  the fixed table gives way to a stacked row-per-player layout with
  large tap targets on the checkboxes and the HC picker; `btn-wmffl`
  + `text-center` wrapper for the submit button per the project's
  button convention. Week picker is a plain GET form with a "Go" button
  (no JS dependency).
- `_lineup_form.html.twig` — the player rows/checkbox markup, shared by
  the member form and the admin override so the two cannot drift.
- `_nav.html.twig` — replaces `activationButtons.php`'s nav pills.
- `templates/admin/activations/{index,edit}.html.twig`.
- `symfony-app/public/js/activations.js` — the live position counters and
  submit-button gating from requirements Decision 3. Plain vanilla JS,
  reads its limits from a `data-lineup-rules` JSON attribute emitted from
  `LineupRules` so the client and server share one definition. Absent JS,
  the form still works.

## 7. Disconnect gameplan / GP from activations

Requirements Decision 8. The activations pages stop touching gameplan;
the table, its rows, and its Doctrine mappings are **kept** for
historical reasons. Nothing here is a port — the remnants below vanish
with the files that hold them, in step 8:

- `processActivations.php`: the `myGP`/`oppGP` `$_REQUEST` reads (lines
  21-22), the commented `$deleteGPs` (line 79) and `$gameplanSql`
  block (lines 100-122) with its `$useGp` flag, and the commented
  `mysqli_query` calls — gone with the file.
- `currentactivations.php`: `$gpLine` (line 42) — gone with the file.
- `symfony-app/public/base/css/activate.css`: the `.gameplanbox` rule —
  the whole file is deleted in step 8, so this needs no separate edit.
- Grep gate on the finished branch: `grep -rni "gameplan\|myGP\|oppGP\|gpLine"`
  over `symfony-app/src`, `symfony-app/templates`, `symfony-app/public/js`,
  and `symfony-app/tests` returns **only** the two preserved mapping
  files (`src/Entity/Gameplan.php`, `src/Enum/GameplanSideEnum.php`) —
  no hits in any activations controller, service, repository, template,
  or script.
- **Do not touch** (historical record — see requirements Decision 8):
  - `symfony-app/src/Entity/Gameplan.php` and
    `symfony-app/src/Enum/GameplanSideEnum.php` — kept as the read path
    for gameplan history. An earlier draft of this plan had them deleted
    as dead code; they were briefly removed and then restored on
    2026-08-20 (`doctrine:mapping:info` back to 62 entities all `[OK]`,
    suite 869 tests). Leave them mapped, and do not let a future
    dead-code sweep pick them up — this is a deliberate keep, not an
    oversight.
  - the `gameplan` table, its rows, and its `schema.sql` definition.
    No migration in this phase touches it.
  - `football/activate/scoreFunctions.php:64-65, 81-84` (Phase 17) and
    `scripts/livescore/updatescores.php:23-24` (Phase 19) — the two
    remaining readers, each owned by its own phase.
- Add a short comment to `Entity/Gameplan.php` recording *why* an
  unreferenced entity is being kept (retired feature, historical rows,
  read path preserved), so the next person to grep for dead mappings has
  the answer in front of them.

## 8. Retire the legacy pages

- Delete `football/activate/{activations,submitactivations,processActivations,currentactivations,activationButtons,index,info,submitthanks}.php`
  and `update2.inc`. **Keep** `currentscore.php` and `scoreFunctions.php`
  on the LegacyBridge for Phase 17.
- Delete the two assets that only `submitactivations.php` loads:
  `symfony-app/public/base/js/activations.js` (dead `swapActivations()`
  → missing `weekSubAct.php`, plus an `eval()`-based XHR helper) and
  `symfony-app/public/base/css/activate.css` (`#subAct` + `.gameplanbox`,
  both obsolete once the table is replaced). Grep first to confirm no
  other page picked them up in the meantime — `currentscore.php` uses
  `score.css`, not these. The new `public/js/activations.js` from step 6
  is a fresh file, not an edit of the old one; give it a distinct enough
  path that no cached copy of the old script can shadow it.
- `symfony-app/src/Controller/LegacyActivationRedirectController.php` —
  301s (with and without `.php`, per the
  `LegacyTransactionRedirectController` precedent; no `index.php` alias,
  that route can never match) from `/activate/activations`,
  `/activate/submitactivations`, `/activate/processActivations`,
  `/activate/currentactivations`, `/activate/submitthanks`,
  `/activate/info` to the new routes.
- Update the main nav (`symfony-app/templates/base.html.twig:70` and
  `football/base/menu.php:103`) from `/activate/activations` to
  `/activations`.

## 9. Tests

`symfony-app/tests/`:

- `Unit/LineupRulesTest.php` — every rule branch: exact-count positions,
  RB/WR/TE ranges, the RB+WR+TE=5 cross-constraint (including the case
  where each position is individually legal but the total is 4 or 6),
  error-message ordering/parity with the legacy strings, and that a
  season override actually changes the accepted counts.
- `Service/ActivationServiceTest.php` — lock boundaries (just before /
  just after `kickoff - 5min`), bye/no-game never locks, all-lock after
  the last kickoff, post-week-14 acquisitions forced to reserve,
  acting-HC eligibility, ownership rejection of a non-rostered id, and
  that save is delete-then-insert inside one transaction.
- `Controller/ActivationControllerTest.php` — logged-out renders the
  login-required page; CSRF-less POST is rejected; an invalid lineup
  re-renders with errors *and* preserves the submitted selections; a
  valid lineup persists and redirects.
- `Controller/Admin/AdminActivationControllerTest.php` — commissioner
  gate, lock bypass, illegal-lineup override requires the explicit
  checkbox.
- `Controller/LegacyActivationRedirectControllerTest.php` — each 301.
- A regression test asserting a crafted POST containing a SQL fragment as
  a playerid is rejected and inserts nothing (the Phase 15 step-3 fix).
- `AdminSeasonControllerTest` addition: lineup fieldset saves and reloads.

## 10. Manual verification

See `validation.md`.
