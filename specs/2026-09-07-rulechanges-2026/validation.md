# Validation — 2026 Rule Changes

Implementation is ready to merge when all of the following hold.

## Automated tests

- [ ] `vendor/bin/phpunit test/` (root, legacy) passes in full.
- [ ] `symfony-app/vendor/bin/phpunit tests/` passes in full.
- [ ] `RosterMoveServiceTest` covers: a season with a small `maxIrSlots`
      still rejecting an over-cap roster move with a clear error; a season
      with a large `maxIrSlots` (or the real 2026 default of 999) allowing
      many IR players; the active-roster-limit check is unaffected either
      way.
- [ ] `PlayerScorerServiceTest` covers: `scoreOffense()` (RB/WR/TE) awards
      3 pts per blocked punt/XP/FG when `off_block` is set; awards 0 when
      `off_block` is `null` (the default — i.e. every season that hasn't
      been given an explicit value, matching every currently-persisted
      historical season); `scoreHC`/`scoreQB`/`scoreK`/`scoreOL` are
      unchanged by nonzero `blockpunt`/`blockxp`/`blockfg` stats
      (regression-proofs the explicit exclusion); `scoreDefense()`/
      `def_block` behavior is unchanged.
- [ ] Legacy scoring tests cover: `scoreOffense()` blocked-kick points, and
      `scoreK()` with the new FG values (2/2/4/6) replacing the old
      (3/4/5/7) expectations.

## Data / migration check

- [ ] Migration adds `max_ir_slots` to `seasons`, default `0`, and confirms
      via a dev-DB query that: 2026 = `999`; 2024-2025 = `1`; 2022-2023 =
      `3`; 2020-2021 = `99`; every season 2019 and earlier = `0`.
- [ ] `/admin/seasons/{season}` renders and saves a `maxIrSlots` value for
      at least one season in manual testing (admin tooling requirement per
      `specs/mission.md`).
- [ ] Cloning a season (`/admin/seasons/clone`) carries `maxIrSlots`
      forward from the season it was cloned from — spot-check the cloned
      row's value in dev DB after triggering a clone.

## Manual / behavioral spot-check

- [ ] On dev, add more than one player to a single team's IR via
      `/transactions/ir` and confirm the roster-move flow
      (`/transactions/confirm`, or wherever the total-roster check fires)
      does not block it for the 2026 season, and that `availableSlots`
      renders a sensible (not negative or nonsensical) number.
- [ ] Confirm a season with a low `maxIrSlots` (e.g. a historical season,
      value `0`) still enforces a cap if exercised through the same flow —
      i.e. the check itself still works, only the 2026 value changed.
- [ ] `football/activate/currentscore.php`'s live scoreboard, for a game
      with a recorded block stat on an RB/WR/TE, shows the extra 3 pts;
      for HC/QB/K/OL with a block stat (if such a row can be constructed),
      confirm no change.
- [ ] `football/activate/currentscore.php`'s live scoreboard reflects the
      new kicker FG point values (2/2/4/6) for a kicker with field goals
      in each distance bucket.

## Explicitly NOT required for merge (documented follow-up, not a blocker)

- [ ] **Not required:** setting `off_block` and `k_fg30`/`k_fg40`/`k_fg50`/
      `k_fg60` for the 2026 season row. Per Josh, this is a fully manual
      step he will do himself via `/admin/seasons` after this branch is
      merged/deployed. Until he does, the blocked-kick-for-offense and
      new-FG-value rules are **not actually in effect for real playerscores**
      even though the code path exists — flag this to him explicitly at
      merge/deploy time so it isn't forgotten.
- [ ] **Not required, and explicitly not wanted:** any recalculation or
      backfill of already-recorded `playerscores` for past weeks/seasons
      under either the blocked-kick or FG-value change.
- [ ] **Not required:** any change to `football/rules/` page text — out of
      scope for this branch per standing convention.
