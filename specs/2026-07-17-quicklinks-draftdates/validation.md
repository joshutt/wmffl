# Phase 10 — Dynamic quicklinks & draft date scheduling: Validation

The phase is done and mergeable when everything below passes. Merge bar
(decision #4 in `requirements.md`): unit tests + manual fake-session E2E;
no functional test suite.

## Automated

Run from `symfony-app/`: `vendor/bin/phpunit tests/ --coverage-clover coverage.xml`

All pre-existing tests still green, plus new unit coverage for:

- **`QuickLinkRepository::findVisible()` window logic**
  - null start + null end → visible
  - start = today and end = today → visible (inclusive bounds)
  - start = tomorrow → hidden; end = yesterday → hidden
  - `active = false` → hidden regardless of window
  - ordering by sortOrder then id
- **`DraftScheduleService::candidateDates()`**
  - every Saturday and Sunday in the range default-checked, weekdays not
  - range spanning a month boundary
- **`DraftScheduleService::applySchedule()` merge semantics**
  - empty season → creates DraftVote (lastUpdate null) per active owner and
    DraftDate (attend Y) per owner × selected date
  - re-run with an added date → only the new rows created
  - re-run with a removed date → those rows deleted
  - existing attend = N and existing lastUpdate values survive a re-run
  - owner added to the season since the first run gets rows on re-run
- **Vote submission max-4-No rule**
  - exactly 4 No → accepted
  - 5 No → rejected, no attend rows changed, lastUpdate not stamped

## Manual E2E (fake-session, `php -S` from `symfony-app/public`)

Use the established fake-session recipe (session must include `fullname`);
one member session and one commissioner session.

### Part A — quicklinks

1. Run the migration on the dev DB; homepage `/` still shows Draft Order /
   Protection Costs / Finances in that order (seed worked, page not blank).
2. `/admin/quicklinks` as commissioner: all three seeds listed.
   As non-commissioner/logged-out: gated like the other admin pages.
3. Add a link with a window ending yesterday → appears in the admin list
   (marked not visible), absent from `/`.
4. Add a link with a window covering today → appears on `/` in sortOrder
   position.
5. Edit a link's label/url → change reflected on `/`.
6. Deactivate a link → gone from `/`, still in admin list; reactivate →
   back on `/`.
7. Delete a link → gone from both.
8. Set every link inactive → the "Other Links" block (heading included) is
   absent from `/`; restore afterwards.
9. `football/quicklinks.php` is deleted; `git grep quicklinks.php` finds no
   live references.

### Part B — draft dates

1. `/admin/draftdates` as commissioner: builder visible. Pick a range;
   step-2 calendar shows every date with Sat/Sun pre-checked.
2. Uncheck one weekend date, check one weekday, submit → tally view shows
   exactly the selected dates, all-Yes, and every team listed as not yet
   voted (`lastUpdate` null).
3. `/draftdate` as a member: the selected dates listed, all radios on Yes.
4. Submit with 2 No → green success alert; radios persist on reload; admin
   tally reflects the Nos; the team leaves the "no vote" list.
5. Submit with 5 No → red alert with the max-4 wording; DB values unchanged
   (previous answers still shown).
6. Re-run the builder adding one date and removing another → member's page
   shows the new date (Yes) and drops the removed one; their earlier No
   votes and lastUpdate are untouched.
7. `/draftdate` logged out → "must be logged in" message, no crash.
8. `/draftdate` for a season with no schedule → friendly empty-state message.
9. Legacy checks: `football/history/*Season/draftdate.php` and all
   `processdraftdate.php` files deleted; hitting an old URL through the
   full stack does not serve a vote form (LegacyBridge miss is acceptable —
   no redirect required per roadmap).

## Data / deploy

- Migration applies cleanly to a copy of prod schema
  (`doctrine:migrations:migrate`) and is reversible (`down` drops
  `quicklinks` only — it must not touch `draftdate`/`draftvote`).
- No changes to `draftdate`/`draftvote` schema — existing hand-inserted
  seasons still render in the admin tally view.
- Deploy note: run the migration, then verify `/` shows the seeded links.
