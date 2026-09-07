# Rule Changes — Code Impact Assessment

Working doc: as each league rule change is described, note here whether it requires code changes, and what.

Status key: 🔧 code change needed · 👀 needs investigation · ✅ no code change (data/commissioner action only)

**Out of scope:** `football/rules/` (the rules pages themselves) — Josh handles those separately. Do not change or suggest changes to files in that directory anywhere in this doc.

---

## 1. IR limit removed (was: max 1 player on IR per team)

**Rule change:** The 1-player-on-IR-at-a-time limit is removed. Teams may carry unlimited players on IR. On/off IR eligibility rules are unchanged.

**Status:** 🔧 code change needed

**Where it's enforced today:**
- `RosterMoveService::getTotalRoster()` (`symfony-app/src/Service/RosterMoveService.php:34`) hardcodes total roster = active limit + 1 ("one IR slot above the active limit").
- `RosterMoveService::executeMoves()` (same file, ~line 278-280) rejects a roster move when `resultingTotal > getTotalRoster()`, with error `"That would give you N players, including IR!  You must drop someone!"`.
- `RosterMoveController::index()` (`symfony-app/src/Controller/RosterMoveController.php:191`) computes `availableSlots` as `min(getTotalRoster() - counts.total, maxPlayers - counts.active)`, capping the slots shown to the IR-inclusive total.
- `transactions/confirm.html.twig:39-41` displays that capped `availableSlots` count to the member.
- `RosterMoveServiceTest.php:58` asserts the old "including IR" over-limit error.

Note: `InjuredReserveService` (the actual add/remove-from-IR logic) has no count cap itself — the limit is entirely a side effect of the roster-total math above, which double-counts IR players against a fixed "+1" ceiling.

**Proposed fix:**
- Remove the `resultingTotal`/`getTotalRoster()` check from `executeMoves()` (keep the active-roster check, since IR still doesn't count against the active limit per the standing rule).
- Drop or repurpose `getTotalRoster()`; update `availableSlots` in the controller to be driven only by `maxPlayers - counts.active`.
- Update `confirm.html.twig` copy so it doesn't imply a fixed total-with-IR cap.
- Update/remove the now-obsolete `RosterMoveServiceTest` assertion for the "including IR" error message.

*(`football/rules/` page text intentionally out of scope for this doc — handled separately.)*

## 2. Blocked kicks = 3 pts for any position (codifying a ~20-year-old rule)

**Rule change:** A blocked punt/XP/FG is worth 3 points, regardless of which position blocked it. Not a new rule — just getting the never-written-down rule list in sync with existing scoring behavior. **Checked, and it is only partially already true.**

**Status:** 🔧 code change needed (existing code only awards this to DL/LB/DB — not "any position")

**Current behavior found in both scoring engines:**
- Legacy `football/base/scoring.php` — `scoreDefense()` (`:137-139`) adds `blockpunt/blockxp/blockfg * 3`. `scoreQB()`, `scoreOffense()` (RB/WR/TE base), `scoreK()`, `scoreOL()`, `scoreHC()` do **not** include it at all, even though every position's stat row carries the same `blockpunt`/`blockxp`/`blockfg` columns (they'd just normally be 0 for those positions). This file is still live — `football/activate/currentscore.php` includes it directly for the current-week live scoreboard.
- Symfony `PlayerScorerService::scoreDefense()` (`symfony-app/src/Service/PlayerScorerService.php:222-225`) mirrors the same gap via the `def_block` rule (`ScoringRuleRegistry.php:97`, group `def`, default 3) — only wired into the DL/LB/DB branch. `scoreHC`, `scoreQB`, `scoreOffense`, `scoreK`, `scoreOL` don't check it. This service is the per-season-configurable engine used by `ScoreCalculatorService` for admin recalcs/backfills.
- No test currently exercises a non-defensive position with a block stat.

So today, an RB/WR/TE credited with a blocked kick (e.g. a fake-punt/field-goal-block special-teams play) scores 0 for it in both engines — confirmed gap for offense, not just a documentation lag. (HC/QB/K/OL are out of scope per Josh — not a factor for those positions.)

**Scope, per Josh: only the general offense block (`scoreOffense`, i.e. RB/WR/TE) and defense (`scoreDefense`, DL/LB/DB). Explicitly excluded: HC, QB, K, OL — not a factor for them, leave those functions untouched.**

**Proposed fix (both engines need to move together, since they're required to stay in sync):**
- `football/base/scoring.php`: add `+= ($scoreArray['blockpunt'] + $scoreArray['blockxp'] + $scoreArray['blockfg']) * 3` to `scoreOffense()` only (in addition to the existing line in `scoreDefense()`). Do not touch `scoreHC()`, `scoreQB()`, `scoreK()`, `scoreOL()`.
- `PlayerScorerService.php`: add the equivalent `blocks` line (using the existing `def_block` rule, generalized off the `def` group so it's not visually scoped to defense-only in the admin UI) to `scoreOffense` only. Do not touch `scoreHC`, `scoreQB`, `scoreK`, `scoreOL`.
- **Forward-looking only, per Josh:** despite the rule having existed informally for ~20 years, do not backfill/recalculate past `playerscores`. Past entries stay as-is; the fix only changes scoring for weeks scored from here forward. This likely means the fix should not just add unconditional lines to the shared functions (which would silently affect any future recalculation of past weeks too) — worth flagging when implementing: if `ScoreCalculatorService`/admin recalc can be re-run against historical weeks, the non-defense blocked-kick line may need to be gated (e.g. by season, or a rule-effective-date) so a future backfill run doesn't retroactively change historical totals. Needs a decision at implementation time on how that gating should work (season cutoff vs. leaving historical recalcs alone).
- Add test coverage: an RB/WR/TE with a nonzero `blockpunt`/`blockxp`/`blockfg` stat should score 3 pts each, for current/future seasons only, in both `ScoreTest`/legacy tests and `PlayerScorerServiceTest`. Confirm HC/QB/K/OL remain unaffected (regression-proof the exclusion).

## 3. Kicker FG points reduced: 0-39=2, 40-49=2, 50-59=4, 60+=6

**Rule change:** Kicker field-goal point values change from the current 3/4/5/7 tiers to 2/2/4/6.

**Status:** ✅ mostly no code change — confirmed. One small exception below.

**Checked:**
- `k_fg30`/`k_fg40`/`k_fg50`/`k_fg60` (`symfony-app/src/Service/ScoringRuleRegistry.php:79-82`) are already season-configurable rule keys, wired into `PlayerScorerService::scoreK()` (`PlayerScorerService.php:147-150`) via `SeasonRuleService`/`ScoringRules`. This is the same mechanism already used for a prior FG60 change (10 → 7, see `Version20260718000000` migration comment) — so for the canonical/historical scoring engine, this is purely an admin action: set the new per-tier values for the season this takes effect at `/admin/seasons`. No code.

**Exception found — legacy live scoreboard is hardcoded, not season-aware:**
- `football/base/scoring.php::scoreK()` (`:110-113`) hardcodes `FG30*3`, `FG40*4`, `FG50*5`, `FG60*7` as plain literals with no per-season lookup at all. This file is still live — `football/activate/currentscore.php` includes it directly to render the current week's in-progress scoreboard.
- The presence of frozen snapshots `scoring2023.php`/`scoring2024.php` (mentioned in the `Version20260718000000` migration) indicates this file has historically been hand-edited in place each time a scoring rule changes — i.e. it always reflects "whatever the current rule is," not a specific season.
- **Fix needed:** update the four literals in `football/base/scoring.php::scoreK()` to `2, 2, 4, 6` when the new rule takes effect, so the live in-progress scoreboard matches the season-rule-driven final scores. This is a plain code edit, not season-configurable — same pattern as past FG-value changes to this file.

## 4. Auto-draft pick logic change

**Rule change:** A fundamental change to how auto-draft selects picks. (Details not yet given — Josh believed this lives in a separate `livedraft` repo/tool, not this one.)

**Status:** ✅ confirmed out of scope — no auto-draft/autopick logic exists in this repo

**Checked:** No pick-selection algorithm anywhere in `football/`, `symfony-app/`, or `src/`. What exists here is all scheduling/record-keeping, not decision logic: `DraftDate`/`DraftScheduleService`/`DraftDateController` (draft day scheduling), `DraftPickRepository`/`DraftPick` (compensation picks for trades), `DraftResultsService`/`AdminDraftResultsController`/`DraftResultsController` (historical results display), `DraftClockStop` (plain timestamp record), `DraftPickHold` (plain team/player hold flag), `DraftVote`. The only textual hit for "auto-draft" in the whole repo is in `football/rules/rules2018.php`, which is out of scope per the standing rules-directory exclusion. Confirmed this repo has nothing to change for this rule — the livedraft tool is a separate codebase.

## 5. Rule changes only take effect if passed before protections are due (else next season)

**Rule change:** A proposal that passes before the protections deadline takes effect that season; otherwise it's deferred to the following season.

**Status:** ✅ no code change needed — confirmed procedural

**Checked:** The Rule Proposals system — `Issue` entity, `ProposalController` (member-facing), `BallotController` (voting), `AdminProposalController` (admin editing). Each `Issue` carries a plain admin-set `season` field (`Issue.php:43`) plus free-form `startDate`/`deadline` fields, all hydrated straight from POST in `AdminProposalController::hydrate()` — nothing computed. `BallotController` (`:129-133`) checks vote totals against `SeasonRuleService::getProposalPassThreshold()`/`getProposalFailThreshold()` to flip the issue's pass/fail status, but never touches `season`, and has no awareness of `ProtectionsService::DEADLINE_CONFIG_KEY` (`protections.deadline`). There is no automatic linkage anywhere between "when a proposal passed" and "which season it's tagged for."

**Conclusion:** this is purely a governance rule about what value gets typed into the existing `season` field when a proposal is administered — the system already supports assigning any proposal to any season by hand, so there's nothing to build or gate. No follow-up needed here.



