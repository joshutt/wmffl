# Requirements — 2026 Rule Changes

## Summary

Three league rule changes, worked out in `specs/rulechanges.md`, land as code
in this branch, effective **2026 (current season, immediately)** —
explicitly overriding the normal "passed after protections deadline defers
to next season" cadence (rule change #5 in that doc) as a one-time
commissioner decision:

1. The 1-player IR limit is removed — teams may carry as many IR players as
   needed. The mechanism that enforced it (roster-total-including-IR cap)
   becomes a separately configurable, per-season safety-valve value instead
   of a hardcoded "+1", defaulted high enough for 2026 to be a non-factor.
2. Blocked punts/XPs/FGs score 3 pts for the general offense scoring block
   (RB/WR/TE) in addition to defense (DL/LB/DB) — forward-looking only, no
   change to already-recorded historical scores. HC/QB/K/OL are explicitly
   unaffected.
3. Kicker field-goal point values change: 0-39yd and 40-49yd both become 2
   pts (from 3/4), 50-59yd becomes 4 pts (from 5), 60+yd becomes 6 pts
   (from 7).

Two other items from `specs/rulechanges.md` require no code and are not part
of this branch: the auto-draft pick-logic change (lives entirely in the
separate `livedraft` repo) and the proposal-timing rule itself (item #5,
already fully supported by existing free-form `season`/`deadline` fields on
`Issue`).

**Out of scope for this branch, per standing convention:** `football/rules/`
page text. Do not touch or propose changes to files in that directory —
Josh maintains the rules pages separately.

## Scope

In scope:

- **Season entity**: new `maxIrSlots` column (int, default 0 — "0 extra
  slots above the active limit," matching literal-zero semantics, i.e. the
  same restrictiveness as if IR didn't exist) alongside the existing
  `maxActivePlayers`. A migration adds the column at `0` for every season,
  then explicitly overrides specific historical ranges plus 2026:
  - 2026: **999** (effectively unlimited — adjust via `/admin/seasons` if a
    different number is preferred)
  - 2024-2025: **1**
  - 2022-2023: **3**
  - 2020-2021: **99**
  - 2019 and earlier: left at the column default, **0**
- **Admin Season UI** (`/admin/seasons/{season}`): add a `maxIrSlots` input
  next to `maxActivePlayers` in the Structure section of
  `admin/seasons/edit.html.twig`, and wire it up in
  `AdminSeasonController::save()` — per mission.md, a new admin-configurable
  value isn't done until admins can manage it without raw DB access.
- **`RosterMoveService`**: replace the hardcoded `getTotalRoster() =
  getMaxActivePlayers() + 1` with `getMaxActivePlayers() + season's
  maxIrSlots`, sourced from `SeasonRuleService`/`Season`. Keep the
  active-roster check unchanged (IR still doesn't count against it).
- **`RosterMoveController`**: `availableSlots` computation and any other
  consumer of `getTotalRoster()` continue to work unchanged in shape, just
  against the new dynamic cap.
- **`transactions/confirm.html.twig`**: no wording change needed beyond
  what already exists (`{{ availableSlots }}` / `{{ counts.ir }}` — the
  copy is generic enough to keep working when the cap is large).
- **`RosterMoveServiceTest`**: update/replace the existing "including IR"
  over-limit assertion to reflect the new dynamic cap (e.g. seed a season
  row with a small `maxIrSlots` to still exercise the over-limit path, plus
  a case confirming a high `maxIrSlots` permits many IR players).
- **`ScoringRuleRegistry`**: add a new key, e.g. `off_block` (group `off`,
  type `int`, **default `null`** — "not awarded" — so every already-stored
  historical season JSON blob, which won't contain this key, safely resolves
  to "not awarded" via `ScoringRules::fromArray()`'s default-merge
  behavior). This is the standard "category not awarded that season"
  pattern already used throughout the registry, and is what makes the
  change forward-only without touching historical data or hardcoding a
  season cutoff in code.
- **`PlayerScorerService::scoreOffense()`**: add a blocked-kicks line (reusing
  the `off_block` rule, same shape as the existing `scoreDefense()` block
  line) — RB/WR/TE only (via `scoreOffense`), not `scoreHC`, `scoreQB`,
  `scoreK`, or `scoreOL`.
- **`football/base/scoring.php`** (legacy, still live for
  `football/activate/currentscore.php`'s current-week scoreboard):
  - `scoreOffense()`: add the same blocked-kicks line as above. This file
    has no per-season awareness at all (it always reflects "the current
    rule"), so unlike the Symfony side there's no historical-safety concern
    here — it only ever renders the current/live week.
  - `scoreK()`: update the four FG literals from `3/4/5/7` to `2/2/4/6`.
- Test coverage (see `validation.md`) for both engines, both changes, and
  the explicit exclusions (HC/QB/K/OL unaffected by blocked-kick scoring).

Out of scope:

- `football/rules/` page text (standing exclusion, not this branch's job).
- Any change to the auto-draft tool (lives in a separate repo).
- Any change to the Rule-Proposal/`Issue`/ballot workflow (item #5 needs no
  code).
- Setting the **new `off_block` value** and the **new `k_fg30`/`k_fg40`/
  `k_fg50`/`k_fg60` values** for the 2026 season row — per Josh, this
  rollout is fully manual. He will set these via `/admin/seasons` himself
  after this branch merges/deploys. This branch does not include a
  migration or seed for those values, and the rule changes are **not
  actually "live"** for real player scores until he does that step (see
  `validation.md`).
- Any recalculation/backfill of already-recorded `playerscores` for past
  weeks or seasons — explicitly not wanted, for either the blocked-kick
  change or the FG-value change.
- Any change to IR eligibility rules (who can go on/off IR, when) — those
  are explicitly unchanged, only the count cap is affected.

## Decisions & context

- **Effective season: 2026, immediately** — a deliberate one-time exception
  to rule #5's normal "next season" cadence, decided by Josh.
- **`maxIrSlots` semantics: literal, not a sentinel.** `0` means "zero extra
  slots above the active limit" (i.e., as restrictive as no-IR-at-all),
  not "unlimited." The "unlimited IR" behavior for 2026 comes entirely from
  the migration setting a large explicit number (999) on that one row, not
  from special-casing zero in the check logic.
- **Historical `maxIrSlots` values are per Josh's explicit direction, not a
  literal preservation of the old hardcoded "+1" rule.** They vary by era
  (2024-2025: 1, 2022-2023: 3, 2020-2021: 99, 2019 and earlier: 0) rather
  than uniformly reflecting what the code actually enforced at the time.
  This is fine because `RosterMoveService::getTotalRoster()` is only ever
  evaluated against the *current* transactional season (there is no
  historical roster-move recalculation feature), so historical rows'
  values are inert bookkeeping/record-keeping, not something that changes
  how the past is displayed or scored.
- **`off_block` uses the registry's existing null-default-means-"not
  awarded" convention**, deliberately chosen so the forward-only
  requirement is enforced by data (no override present on old season rows)
  rather than a hardcoded season-cutoff `if` in `PlayerScorerService`. This
  matches how every other "category not awarded that season" case already
  works in this codebase.
- **Scope of blocked-kick scoring is deliberately narrow**: general offense
  (`scoreOffense`, i.e. RB/WR/TE) and defense only. HC, QB, K, and OL are
  explicitly excluded per Josh — "won't be a factor for them."
- **Kicker FG value change has no Symfony-side code change** — `k_fg30`
  through `k_fg60` are already season-configurable; only the legacy
  hardcoded scoreboard literals need editing.
