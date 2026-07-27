# Plan — Rule Proposals redesign + full UI (Phase 10.6, expanded)

Task groups are ordered so each lands as reviewable work; roughly one PR-sized
slice each, though this phase is large enough it may merge as several commits
on the one branch. Backfill (group 6) is generated and reviewed but **not**
applied to prod until Josh signs off on its report.

## 1. Schema migration

1. New migration `Version2026072700xxxx`:
   - `issues`: widen `IssueName` → `varchar(120)`; add `Rationale text NULL`,
     `RuleChangeText text NULL`, `Published tinyint(1) NOT NULL DEFAULT 0`;
     add `Status enum('Open','Passed','Rejected','Withdrawn') NOT NULL DEFAULT
     'Open'` and backfill it from `Result` (PASS/Passed→Passed;
     REJECT/REJECTED/Rejected→Rejected; else Open) **before** dropping
     `Result`; set existing rows `Published = 1` (they were already public);
     drop `Sponsor int`.
   - Create `issue_sponsors` (`IssueID`, `UserID`, `SortOrder`,
     `PRIMARY KEY (IssueID, UserID)`, FK → `issues` cascade, FK → `user`).
   - `seasons`: add `proposal_pass_threshold decimal(5,4) NOT NULL DEFAULT
     0.5100` and `proposal_fail_threshold decimal(5,4) NOT NULL DEFAULT
     0.5100`. Then seed the era split: `UPDATE seasons SET
     proposal_pass_threshold = 0.6700 WHERE season < 2022` (2022+ and future
     stay at the `0.5100` default). Fail threshold is `0.5100` for all.
   - Pre-check `ballot` for orphans (issue/team ids with no parent); if clean,
     add `FK_ballot_issue` and `FK_ballot_team`. If orphans exist, abort and
     report to Josh (do not delete).
2. Provide a working `down()` (drop FKs, drop `issue_sponsors`, re-add
   `Sponsor`/`Result`, re-narrow `IssueName`, drop new columns).
3. Regenerate `scripts/database/schema.sql`.

## 2. Entities & repositories

1. `App\Entity\Issue` — map all columns incl. `Status` (string-backed enum or
   PHP enum `IssueStatus`), `Published` bool, `Rationale`, `RuleChangeText`;
   `OneToMany` to `IssueSponsor` ordered by `SortOrder`.
2. `App\Entity\IssueSponsor` — composite key (`Issue`, `User`), `SortOrder`;
   `ManyToOne` to `Issue` and `User`.
2a. `Season` entity: add `proposalPassThreshold` / `proposalFailThreshold`
   getters/setters; surface both through `SeasonRuleService` (alongside
   `winPercent`/`postPercent`) and add the two fields to the `/admin/seasons`
   form + template (`AdminSeasonController`).
3. `App\Entity\Ballot` — map for the new FK relations (or update if it exists).
4. `IssueRepository` — `findPublishedBySeason()`, `findOpenBallotIssues($team)`,
   `findPendingForAdmin()`, plus season list for the selector.
5. Unit tests for entity mapping / repository queries.

## 3. Markdown rendering

1. `composer require league/commonmark` in `symfony-app`.
2. `App\Service\MarkdownService::toHtml(string): string` — CommonMark with
   `html_input => escape`, `allow_unsafe_links => false`.
3. `App\Twig\MarkdownExtension` — `markdown_to_html` filter.
4. Unit tests: escaping of raw HTML, links, and multi-level blockquote →
   nested list/quote rendering.

## 4. Member UI — proposals list & submit

1. `ProposalController`:
   - `/rules/proposals` (default current season) + `?season=YYYY` — published
     issues only, grouped/ordered by `IssueNum`, sponsors linked to profiles,
     rule-change rendered via `markdown_to_html`, season selector.
   - `/rules/proposals/submit` (auth-gated) — form (name, description,
     rationale markdown, rule-change markdown) with live/preview; writes a
     pending row (`Status=Open`, `Published=0`, current season), submitter as
     `issue_sponsors` SortOrder 0; emails commissioner via `symfony/mailer`
     (`null://` in dev). Replaces `propose.php`/`proposesubmit.php`.
2. Templates for both; `btn-wmffl` + `text-center` conventions.
3. Controller/functional tests (fake-session recipe).

## 5. Ballot flow + admin

1. `BallotController` — port `ballot.php`/`ballotcount.php`:
   - Show open, **published** issues for the logged-in team; **bind all
     parameters** (closes the `ballotcount.php` injection).
   - Read pass/fail thresholds from the proposal's season via
     `SeasonRuleService` (no more hardcoded `.67`/`.51`); commissioner email on
     a threshold crossing; port the issue-87 custom-label quirk as an isolated
     special case (or a small per-issue label map).
   - Confirmation page listing cast votes (replaces `ballotcount.php` output).
2. `AdminProposalController` (`/admin/proposals`):
   - List all issues incl. unpublished; **approve/publish** pending;
     create/edit every field incl. rule-change markdown and `Status`; manage
     ordered co-sponsors (resolved to users); withdraw/void.
   - `btn-wmffl` + `text-center` buttons.
3. Functional tests: submit → admin approve → appears on list & ballot; a vote
   crossing threshold; injection attempt is neutralised.

## 6. Historical backfill (generated, reviewed, not auto-applied)

1. One-time parser script in `scripts/` over `football/rules/proposals*.php`:
   - Handle both markup eras (DOMDocument): extract IssueNum, IssueName,
     sponsor string(s), status blurb, rationale prose, rule-change HTML.
   - Convert rule-change HTML → Markdown via `league/html-to-markdown`.
   - Reconcile per decision 1: match existing `issues` by `IssueNum`(+Season),
     **enrich only empty** target fields, **insert** when no match; record any
     page-vs-existing conflict on a non-empty field instead of overwriting.
   - Resolve sponsors per decision 2, in order: (0) `Commissioner` → the user
     Josh Utterback; (a) exact `user.Name` → link;
     (b) else match `teamnames.name`/`abbrev` for the proposal's `Season` →
     look up `owners` for that teamid + season + `primary=1` → link that
     owner's userid; (c) no sponsor on page → leave null (not flagged);
     (d) still unresolved (unknown name, no teamnames/owners row that season,
     ambiguous) → flag. No fuzzy linking.
   - Emit (a) idempotent SQL under
     `scripts/database/migration/2026-07-27-issues-backfill.sql` and (b) a
     human-readable **backfill report** (inserted vs. enriched counts,
     unresolved sponsors with their page context, field conflicts).
2. Josh reviews the report; unresolved sponsors / conflicts resolved manually;
   SQL applied to prod at deploy — **not** part of the code-merge gate.

## 7. Legacy retirement & wiring

1. Delete `football/rules/proposals*.php`, `propose.php`,
   `proposesubmit.php`, `ballot.php`, `ballotcount.php`, `ballotthanks.php`,
   and the proposals part of `index.php`. **Keep** `rules{year}.php`,
   `RulesSup*.php` (Phase 14).
2. `LegacyRulesRedirectController` — 301s: `proposals{year}.php` →
   `/rules/proposals?season=YYYY`, `ballot(.php)` → new ballot route,
   `propose*(.php)` → submit route. Verify LegacyBridge no longer serves the
   deleted paths (it 500s on missing includes — see history-phase9a note).
3. Update any nav/menu links (`base/menu.php`, Symfony nav) pointing at the old
   ballot/proposals URLs.

## 8. Validation pass

Execute `validation.md` end-to-end: migration up/down, unit + functional
suites with clover coverage, fake-session E2E walkthrough, backfill report
review, `btn-wmffl` spot check, and legacy-redirect verification.
