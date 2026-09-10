# Validation — Activations UI modernization (Phase 15)

## Automated

- Full suite green: `cd symfony-app && vendor/bin/phpunit tests/
  --coverage-clover coverage.xml` (per the `feedback_test_coverage`
  convention — never `--coverage-text`). Baseline entering this phase:
  869 tests.
- All new tests from `plan.md` step 9 pass, specifically including:
  - the SQL-injection regression test (a POST whose `RB[]` value is a
    SQL fragment inserts nothing and returns a validation error),
  - the ownership test (a POST naming a player on another team's roster
    is rejected),
  - the lock-boundary tests either side of `kickoff - 5 minutes`,
  - the RB+WR+TE = 5 cross-constraint tests.

## Migration

- `cd symfony-app && php bin/console doctrine:migrations:migrate` applies
  cleanly against the dev DB. Note: `doctrine:schema:update --dump-sql`
  is not usable as a diff check in this repo (pre-existing `Unknown
  database type enum requested` error, unrelated) — verify instead with
  `SHOW COLUMNS FROM seasons LIKE 'lineup_rules'`.
- Every existing `seasons` row (1992–2026) has a populated
  `lineup_rules` JSON after migrating up — `SELECT COUNT(*) FROM seasons
  WHERE lineup_rules IS NULL OR JSON_LENGTH(lineup_rules) = 0` returns 0.
- `down()` genuinely reverses: migrate up, down one step (column gone),
  up again (column back, populated) — not a one-way trip.
- `scripts/database/schema.sql` updated to match.
- **Deploy step for Josh**: this migration must run on staging and prod
  at deploy time.

## Manual E2E

Server started per the `gotcha_php_s_dotted_paths` note:
`php -S localhost:8000 -t public public/index.php` from `symfony-app/`.
Use a fake owner session for the member flows and a fake commissioner
session for the admin ones.

### Submit flow (member)

1. **Logged out**: `/activations/submit` renders the login-required page,
   not a 500 and not an empty form.
2. **Roster parity**: for a real team in the current week, the
   Starters/Reserves split matches what legacy
   `/activate/submitactivations.php` renders for the same team/week
   (compare against the pre-deletion legacy page, or a saved screenshot,
   before the files are removed) — same players, same positions, same
   NFL teams, same opponent strings including `Bye` and `vs`/`@`
   direction, same injury labels.
3. **Locked player**: a player whose kickoff has passed shows as locked
   (no editable checkbox) and his current state survives a submit —
   confirm in the DB that his `activations` row is unchanged after
   saving an otherwise-different lineup.
4. **Lock boundary**: with a player whose kickoff is >5 minutes out, the
   checkbox is editable; past that point (adjust the `nflgames.kickoff`
   in the dev DB to test) it is locked. Confirms the 5-minute rule, not
   the old 2-hour or the view's old 30-minute value.
5. **Bye week**: a player with no game this week is never locked and can
   be freely activated/deactivated.
6. **Post-week-14 acquisition**: a player acquired after week 14's
   `weekmap.ActivationDue` appears in Reserves and cannot be activated,
   even though his kickoff is in the future.
7. **Acting HC**: for a team whose rostered HC has no game this week, the
   acting-HC picker appears, lists only unrostered HCs kicking off more
   than 30 minutes out, and saving writes an `HC` row for the chosen
   free agent.
8. **Invalid lineup**: submit 3 RB / 1 WR / 1 TE. Confirm the form
   re-renders with the position errors, **every checkbox the user ticked
   is still ticked**, and nothing was written to `activations`
   (row count for that season/week/team unchanged).
9. **Flex constraint**: submit 1 RB / 2 WR / 1 TE (each individually
   legal, total 4). Confirm the "1 RB, 2 WR, 1 TE and 1 flex" error
   fires and nothing is written.
10. **Valid lineup**: submit a legal lineup. Confirm redirect to
    `/activations`, a success flash, and exactly the expected rows in
    `activations` for that season/week/team (old rows gone, new rows
    present, correct `pos` per player).
11. **SQL injection**: POST `RB[]=1) ; DROP TABLE activations; --`
    (via curl with a valid CSRF token). Confirm a validation error, the
    `activations` table still exists, and no partial rows were written.
12. **Ownership**: POST a playerid belonging to a different team's
    roster. Confirm rejection with a friendly error.
13. **Week switcher**: change the week and submit the picker's "Go".
    Confirm the roster reloads for that week without JS — this is the
    replacement for `swapActivations()`, which has been firing at a
    nonexistent `weekSubAct.php` (verify with JS disabled in the
    browser). This is a fix, not parity: the legacy dropdown never
    reloaded anything.
14. **JS counters**: with JS enabled, ticking a 4th WR shows the counter
    in a violation state and disables the submit button; untick and the
    button re-enables. With JS disabled, the button is always enabled
    and the server does the rejecting (step 8 still passes).

### Current activations view

15. **Parity**: `/activations` for the current week shows the same teams
    and players as the legacy `/activate/activations.php` did, with the
    `*` lock markers now at the 5-minute threshold (a player 20 minutes
    from kickoff is *not* starred here, where legacy starred him — this
    is the intended Decision 5 change; confirm it's the only marker
    difference).
16. **Matchup pairing**: each block shows the two teams of one scheduled
    game together; every game on the week's `schedule` appears exactly
    once and no team appears twice.
17. **Mobile**: at 375px wide, no horizontal page scroll, cards stack to
    one column, and the text is readable without zoom. Same check at
    768px (2-up) and 1280px.
18. **Past week**: `/activations?week=<earlier week>` renders that week's
    lineups.

### Admin override

19. **Gate**: `/admin/activations` without a commissioner session
    redirects to `/`.
20. **Lock bypass**: pick a team and a week whose games have all kicked
    off. Confirm every player is still editable and saving persists —
    the thing `Become Team` cannot do.
21. **Rules still enforced**: save an illegal lineup *without* ticking
    "save anyway". Confirm rejection with the same error list the member
    form produces.
22. **Deliberate illegal save**: tick "save anyway", save the same
    illegal lineup. Confirm it persists, and that
    `/admin/toomanyplayers` (or the existing illegal-lineup fine
    reporting) picks it up as expected.
23. **Nav**: "Activations" appears in the admin nav with correct active
    state on all `/admin/activations*` pages.

### Season rules

24. `/admin/seasons/{current}` shows the new Lineup fieldset with the
    current limits; changing WR max to 4, saving, and reloading
    `/activations/submit` allows a 4th WR (then revert). Proves the
    registry is genuinely the single definition consumed by form,
    validator, and JS.

### Legacy retirement

25. Each retired URL 301s to its new route, with and without `.php`:
    `/activate/activations`, `/activate/submitactivations`,
    `/activate/processActivations`, `/activate/currentactivations`,
    `/activate/submitthanks`, `/activate/info`.
26. **Phase 17 boundary intact**: `/activate/currentscore.php?teamid=..&season=..&week=..`
    still renders through the LegacyBridge — this phase must not break
    the box score.
27. `grep -rn "activate/" football/ symfony-app/templates/` finds no
    remaining links to the deleted pages (nav updated in both
    `symfony-app/templates/base.html.twig` and `football/base/menu.php`).
28. `football/activate/` contains only `currentscore.php` and
    `scoreFunctions.php`.
29. Old assets gone: `symfony-app/public/base/js/activations.js` and
    `symfony-app/public/base/css/activate.css` no longer exist, and a
    hard-reloaded `/activations/submit` shows no 404s for them in the
    browser network tab (nothing else was still loading them).

### Gameplan / GP disconnection (requirements Decision 8)

30. `grep -rni "gameplan\|myGP\|oppGP\|gpLine" symfony-app/src
    symfony-app/templates symfony-app/public/js symfony-app/tests`
    returns **only** `src/Entity/Gameplan.php` and
    `src/Enum/GameplanSideEnum.php` (the preserved mappings, step 33) —
    no GP concept was carried into any ported activations code, in any
    form, including comments.
31. Submitting a lineup writes rows to `activations` and to no other
    table: `SELECT COUNT(*) FROM gameplan` is identical before and after
    a successful submit (member form) and a successful admin override
    save.
32. A POST carrying `myGP=1&oppGP=2` alongside a valid lineup is accepted
    and simply ignores those fields — no gameplan row, no error, no
    crash (proves the fields are gone, not merely hidden from the form).
33. **Mappings preserved**: `symfony-app/src/Entity/Gameplan.php` and
    `symfony-app/src/Enum/GameplanSideEnum.php` still exist and still
    map cleanly — `php bin/console doctrine:mapping:info` lists
    `App\Entity\Gameplan` as `[OK]`. This is the check that the phase
    did *not* over-delete; gameplan history stays queryable from
    Symfony.
34. **Historical GP data intact and still rendering**:
    `/activate/currentscore.php` still shows its `GP+`/`GP-` markers from
    the existing `gameplan` rows (Phase 17 owns that page), and
    `SELECT COUNT(*) FROM gameplan` matches its pre-phase value exactly
    — this phase deletes no historical GP data and drops no GP schema.

## Merge gate

- All automated tests green (no regressions against the 869 baseline),
  all manual steps above confirmed by Josh.
- Per `feedback_spec_commit_approval`: do not self-commit changes to
  these three spec docs — wait for explicit approval each time they're
  revised.
