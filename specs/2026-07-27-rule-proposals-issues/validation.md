# Validation — Rule Proposals redesign + full UI (Phase 10.6, expanded)

The phase is merge-ready when every check below passes. Tests use the
project's fake-session recipe for auth'd flows and
`--coverage-clover coverage.xml` (never `--coverage-text`).

## 1. Migration

- [ ] `php bin/console doctrine:migrations:migrate` applies cleanly on a copy
      of the dev DB.
- [ ] `down()` reverses it cleanly (columns/tables/FKs restored).
- [ ] Post-migration schema check:
  - `issues.IssueName` is `varchar(120)`; `Rationale`, `RuleChangeText` (text,
    nullable), `Published tinyint(1) DEFAULT 0` present; `Status
    enum('Open','Passed','Rejected','Withdrawn')` present; `Sponsor` and
    `Result` gone.
  - Existing 136 rows: `Status` correctly mapped from old `Result`
    (spot-check a PASS/Passed, a REJECT/REJECTED, and a null → Open);
    `Published = 1` on all pre-existing rows.
  - `issue_sponsors` exists with the two FKs and PK `(IssueID, UserID)`.
  - `seasons` has `proposal_pass_threshold` / `proposal_fail_threshold`
    (decimal 5,4). `proposal_pass_threshold` is `0.6700` for seasons < 2022
    and `0.5100` for seasons >= 2022 (spot-check 2021 and 2022);
    `proposal_fail_threshold` is `0.5100` for every season.
  - `ballot` has `FK_ballot_issue` and `FK_ballot_team`; no orphan rows were
    silently deleted (orphan pre-check ran and was clean, or was reported).
- [ ] `scripts/database/schema.sql` regenerated to match.

## 2. Unit tests

- [ ] `MarkdownService`: raw `<script>`/HTML in input is **escaped** (not
      emitted); a plain link renders; a multi-level nested blockquote (as in
      2026.3 "Blocked Kicks") renders as nested quotes without raw HTML leaking.
- [ ] Proposal parser: extracts IssueNum/name/sponsor(s)/status/rationale/
      rule-change from **both** markup eras (2005 `<p><b>` format and 2026 card
      format); rule-change HTML → Markdown conversion round-trips sensibly;
      a sponsor of **`Commissioner`** resolves to Josh Utterback's user;
      a proposal with **no sponsor** resolves to null (not flagged); one with
      a **team-name sponsor** resolves via `teamnames` (that season's name) →
      `owners` (team + that season + `primary=1`) to the owning user; a team
      with no `teamnames`/`owners` row for that season is flagged.
- [ ] Status/Result mapping helper covers PASS/Passed/REJECT/REJECTED/typo/
      null.
- [ ] Entity mappings: `Issue`↔`IssueSponsor` ordered by `SortOrder`;
      `Ballot` FK relations load.

## 3. Functional / E2E (fake session)

- [ ] **Submit → pending:** authed member submits a proposal → a row exists
      with `Published=0`, `Status=Open`, current season, submitter as
      `issue_sponsors` SortOrder 0; commissioner email dispatched (caught by
      the mailer in test env); the proposal does **not** appear on the public
      list or ballot yet.
- [ ] **Admin approve:** admin publishes it → `Published=1`; it now appears on
      `/rules/proposals` and (if open/in-window) on the ballot.
- [ ] **Proposals list:** `/rules/proposals` shows current-season published
      issues with sponsors linked, rule-change markdown rendered; `?season=YYYY`
      shows a historical season; unpublished rows are absent.
- [ ] **Ballot:** an authed team sees open published issues, casts votes, sees
      the confirmation of cast votes; a vote crossing the threshold triggers
      the commissioner email; the issue-87 custom labels render correctly.
- [ ] **Per-season thresholds:** editing `proposal_pass_threshold` /
      `proposal_fail_threshold` on `/admin/seasons` changes the pass/fail
      point the ballot uses for that season (assert the crossing shifts with a
      changed threshold).
- [ ] **Injection neutralised:** posting a crafted `issueid`/`vote` to the
      ballot endpoint cannot alter unintended rows (parameters bound); a
      regression test asserts this.
- [ ] **Admin CRUD:** create/edit an issue incl. rule-change markdown, status,
      and reordered co-sponsors; withdraw/void works.

## 4. Legacy retirement

- [ ] `football/rules/{proposals*,propose,proposesubmit,ballot,ballotcount,
      ballotthanks}.php` deleted; `rules{year}.php` and `RulesSup*.php`
      **retained**.
- [ ] 301s resolve: `proposals2026.php` → `/rules/proposals?season=2026`,
      `proposals2005.php` → `?season=2005`, `ballot(.php)` → new ballot route,
      `propose*(.php)` → submit route. Verify via curl/functional test that the
      LegacyBridge no longer 500s on the deleted paths.
- [ ] Nav/menu links point at the new routes.

## 5. Historical backfill report (reviewed, gated separately)

- [ ] `scripts/database/migration/2026-07-27-issues-backfill.sql` generated and
      **idempotent** (re-runnable; enriches only empty fields; never
      overwrites a non-empty existing value).
- [ ] Backfill report produced listing: rows inserted vs. enriched (by season),
      every **unresolved sponsor** with its page context (unknown name, team
      with no owner row that season, ambiguous), and every **field conflict** (page disagrees with a non-empty
      existing value).
- [ ] Josh has reviewed the report; unresolved sponsors and conflicts resolved
      manually. **This SQL is applied to prod at deploy — it does NOT gate the
      code merge.** No destructive action taken without Josh's confirmation.

## 6. Suite & conventions

- [ ] Full PHPUnit suite green: `symfony-app/vendor/bin/phpunit tests/
      --coverage-clover coverage.xml` (pre-existing legacy `/test/` failures
      excepted).
- [ ] All new member/admin buttons use `btn-wmffl` + `text-center`.
- [ ] `php bin/console lint:twig` and `lint:container` pass.

## Deploy notes (record in roadmap "Done" on completion)

- Migration `Version2026072700xxxx` (schema + FKs) runs at deploy.
- The `2026-07-27-issues-backfill.sql` runs **after** Josh's report review —
  separate from the migration.
- Confirm the commissioner/proposal notification `MAILER_DSN` is set in prod
  `.env.local` (same dependency as Phase 8 trades).
