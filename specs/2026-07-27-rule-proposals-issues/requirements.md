# Rule Proposals — `issues` redesign + full UI (Phase 10.6, expanded)

## Summary

Phase 10.6 as scoped on the roadmap was a **data-layer-only** redesign of the
`issues` table (widen columns, add `Rationale`/`RuleChangeText`, a proper
`Status` enum, an `issue_sponsors` join table, CommonMark rendering, and
`ballot` FK constraints), with the actual proposal UI deferred to Phase 14 and
**no** historical data migration.

Two decisions from Josh expand that scope for this phase:

1. **The full proposal UI is pulled into this phase** — member submission,
   proposal display (replacing the 26 hand-written `proposals{year}.php`
   pages with a single data-driven route), the ballot/voting flow, and admin
   management. Phase 14's `rules` item shrinks to just the static rulebook
   pages (`rules{year}.php`) accordingly.
2. **Historical proposals are backfilled** from the existing
   `football/rules/proposals{year}.php` pages into `issues` /
   `issue_sponsors`, rather than starting forward-only.

The result: rule proposals become a fully Symfony-served, admin-manageable
feature with its history preserved, and `football/rules/` proposal/ballot pages
are retired.

## Current state (legacy)

- **`issues` table** (`IssueID, IssueNum varchar(10), IssueName varchar(40),
  Sponsor int, Description tinytext, Season year, Deadline date, StartDate
  date, Result varchar(10)`), 136 rows, backs the ballot. It was never rich
  enough to reproduce what the proposal pages show: `Sponsor` is a single int
  (can't hold co-sponsors), `Description` is a short paraphrase, there is **no**
  column for the rule-change text (only hand-written `<blockquote><i>` HTML on
  the page), and `Result` is a free `varchar(10)` with inconsistent values
  (`PASS`/`Passed`/`REJECT`/`REJECTED`/typos).
- **`ballot` table** (`TeamID, IssueID, Result tinyint, Vote
  enum('Accept','Reject','Abstain','No Vote')`, `PRIMARY KEY (IssueID,
  TeamID)`) — vote tallies, **no FK constraints at all**.
- **`football/rules/`** proposal surface:
  - `proposals{year}.php` (2000–2026, ~26 files) — hand-authored proposal
    lists. Two markup eras: an older `<p><b>Proposal…</b><br/><b>Sponsor:…</b>
    <br/><font>Status:…</font>…<blockquote><i>rulechange</i></blockquote></p>`
    format (≈2005) and a newer Bootstrap-card format (2026). Both hold
    proposal number, name, sponsor(s), a status blurb, rationale prose, and a
    rule-change block.
  - `propose.php` / `proposesubmit.php` — member submit form that only
    **emails** `proposals@wmffl.com`; it never writes to `issues`.
  - `ballot.php` / `ballotcount.php` — the voting flow. `ballotcount.php` has a
    **SQL-injection hole** (`update ballot set vote='$value' where
    issueid='$key'` interpolating `$_POST`) and derives pass/fail thresholds
    (`PASS_THRES = .67`, `FAIL_THRES = .51`) with commissioner emails.
  - `index.php`, `ballotthanks.php` — index/thanks pages.
- `rules{year}.php` (static rulebook) and `RulesSup*.php` are **out of scope** —
  they stay for Phase 14.

## Decisions (from Josh)

1. **Backfill reconciliation — enrich existing, insert missing.** Match each
   parsed proposal to an existing `issues` row by `IssueNum` (+ `Season`); fill
   `Rationale` / `RuleChangeText` / `Status` / sponsors in place when those
   target fields are empty. Insert a new row only when no match exists. **Never
   overwrite a non-empty existing field** — if the page content disagrees with
   an existing non-empty value, flag it in the backfill report for Josh rather
   than clobbering. Any step that would delete or overwrite existing data is
   confirmed with Josh first.
2. **Sponsor resolution — user, then team-owner, else null/flag.** For each
   parsed sponsor string, in order:
   0. **Special case:** a sponsor of `Commissioner` (role label, not a name)
      resolves to the user **Josh Utterback**. Other role labels remain out of
      scope (flagged, not linked).
   1. Match against `user.Name`. Clean unambiguous match → link that `UserID`.
   2. Otherwise match against the **season-specific team name** in `teamnames`
      (`name`/`abbrev` for the proposal's `Season`) — not the current
      `team.Name`, since team names change over time. On a match, resolve the
      **owner of that team for that season** via `owners` (`teamid` = matched
      team, `season` = the proposal's `Season`, `primary = 1`) and link that
      owner's `userid`. This covers the many older pages that credit a **team
      name** ("Gallic Warriors", "MeggaMen").
   3. **No sponsor on the page** → leave it null (no `issue_sponsors` row); this
      is not a "miss" and is not flagged.
   4. Anything still unresolved — unknown name, no `teamnames` row for that
      season, a team with no `owners` row for that season, or an ambiguous
      match — is not guessed: skip the link and record it in the backfill
      report for Josh to resolve manually.
   No fuzzy auto-linking at any step.
3. **Member submit — write pending, admin approves.** The submit form writes a
   real `issues` row but in an unpublished/pending state (Status `Open`,
   `Published = 0`), with the submitter recorded as the first sponsor. It stays
   hidden from the public proposal list and ballot until an admin approves it
   (`Published = 1`). The commissioner is emailed on submit.
4. **Validation — full E2E + unit + reviewed backfill report.** Migration
   up/down + FK verification; PHPUnit for the markdown service, the proposal
   parser, and entity mappings; a fake-session E2E walkthrough of
   submit → approve → display → ballot → admin; and a **backfill report**
   (rows inserted vs. enriched, unresolved sponsors, content conflicts)
   reviewed by Josh before the backfill is applied to prod.

## Target schema

### `issues` (altered)

- `IssueName` → `varchar(120)` (widened from 40).
- **Add** `Rationale text NULL` — full rationale prose (the page's paragraph(s)),
  kept alongside the existing short `Description`.
- **Add** `RuleChangeText text NULL` — the proposed rule-change text, stored as
  **Markdown** (`html_input => escape` at render time).
- **Replace** `Result varchar(10)` → `Status enum('Open','Passed','Rejected',
  'Withdrawn') NOT NULL DEFAULT 'Open'`. Legacy `Result` values map:
  `PASS`/`Passed` → `Passed`; `REJECT`/`REJECTED`/`Rejected` → `Rejected`;
  anything else / null → `Open`. (Withdrawn has no legacy source; used going
  forward.)
- **Add** `Published tinyint(1) NOT NULL DEFAULT 0` — admin-approval gate,
  **orthogonal** to `Status`. `Status` is the voting lifecycle; `Published`
  is "visible to members." Keeping the roadmap's 4 enum values intact and
  modelling the pending gate as a separate boolean avoids overloading the
  vote-outcome enum. Backfilled historical rows are `Published = 1`.
- **Drop** `Sponsor int` (replaced by `issue_sponsors`).
- Keep `IssueID`, `IssueNum`, `Description`, `Season`, `Deadline`, `StartDate`.

### `issue_sponsors` (new)

- `IssueID int NOT NULL` — FK → `issues.IssueID` (cascade delete).
- `UserID int NOT NULL` — FK → `user.UserID`.
- `SortOrder int NOT NULL DEFAULT 0` — display order (first sponsor = 0).
- `PRIMARY KEY (IssueID, UserID)`.
- Supports any number of ordered co-sponsors. Unresolved historical sponsors
  are **not** stored here (they land in the backfill report instead), so no
  free-text sponsor column — a deliberate consequence of the "flag misses"
  decision.

### `seasons` (two new columns — per-season ballot thresholds)

The ballot pass/fail thresholds are hardcoded in legacy `ballotcount.php`
(`PASS_THRES = .67`, `FAIL_THRES = .51`). These can change over time, so they
move into the existing `seasons` table alongside the other per-season typed
rule columns (`win_percent`, etc.), read through `SeasonRuleService` and edited
on the existing `/admin/seasons` form.

- **Add** `proposal_pass_threshold decimal(5,4) NOT NULL DEFAULT 0.5100`.
- **Add** `proposal_fail_threshold decimal(5,4) NOT NULL DEFAULT 0.5100`.
- Same `decimal(5,4)` shape as the existing `*_percent` columns.
- **Seeding is era-specific for the pass threshold:** the league lowered it
  from `.67` to `.51` starting in 2022. The migration seeds
  `proposal_pass_threshold = 0.6700` for seasons **< 2022** and `0.5100` for
  seasons **>= 2022**; new future seasons default to `0.5100`. The
  `proposal_fail_threshold` is `0.5100` for every season. (Legacy
  `ballotcount.php` hardcoded `.67` — correct only for pre-2022 ballots.)
- Surfaced via `Season` entity getters/setters, exposed through
  `SeasonRuleService` (as it already does for `winPercent`/`postPercent`), and
  editable on the admin seasons form. The ballot flow reads them for the
  proposal's season instead of the two constants.

### `ballot` (constraints only, structure unchanged)

- Add `FK_ballot_issue` (`IssueID` → `issues.IssueID`).
- Add `FK_ballot_team` (`TeamID` → `team.TeamID`).
- Vote tallies stay **derived** via `GROUP BY Vote` — not duplicated into
  `Status`.
- Precondition: verify no orphan `ballot` rows exist before adding FKs; if any
  do, surface them to Josh (do not silently delete).

## Markdown rendering

- Add `league/commonmark` to `symfony-app/composer.json` (not currently
  installed; `league/html-to-markdown` and `twig/markdown-extra` already are).
- A small `MarkdownService` renders `RuleChangeText` (and `Rationale` if it too
  is authored as markdown) to HTML with `html_input => escape` and
  `allow_unsafe_links => false` — proposal authors are members, not admins, so
  input must be escaped.
- A Twig filter (`|markdown_to_html|raw`) exposes it to templates.
- The historical rule-change blocks are **HTML** (`<blockquote><i>…`), not
  markdown. The backfill parser converts them to Markdown for storage in
  `RuleChangeText` using the already-present `league/html-to-markdown`, so the
  stored column is uniformly markdown regardless of era.

## UI scope (member + admin)

### Member-facing

- **Proposals list** — a single data-driven route (e.g.
  `/rules/proposals` defaulting to the current season, `?season=YYYY` for
  history) replacing all 26 `proposals{year}.php` files. Renders proposal
  number, name, sponsor(s) (linked to profiles where resolved), status,
  rationale, and the markdown-rendered rule-change block, grouped/ordered by
  `IssueNum`. Only `Published = 1` rows show.
- **Submit a proposal** — authenticated form (replaces `propose.php` /
  `proposesubmit.php`) writing a pending `issues` row (decision 3), submitter
  as first sponsor, commissioner emailed. Rationale + rule-change entered as
  markdown with a preview.
- **Ballot** — port `ballot.php` / `ballotcount.php`: show open, published
  issues the team hasn't finished voting; **bind all parameters** (fix the
  injection); keep the per-issue custom-label quirk (issue 87 "10 Teams / 12
  Teams") data-drivenly or as an isolated special case; keep the pass/fail
  threshold + commissioner email on threshold crossing, reading the thresholds
  from the proposal's season (`SeasonRuleService`) rather than the removed
  constants.

### Admin

- Admin proposal management (`/admin/proposals` or similar): list all issues
  incl. unpublished; **approve/publish** pending submissions; create/edit
  (name, num, season, deadline/start, description, rationale, rule-change
  markdown, status); manage co-sponsors (add/remove/reorder, resolved to
  users); void/withdraw. Follows the `btn-wmffl` + `text-center` button
  convention. Satisfies the mission's "admin tools in step" requirement.

## Out of scope

- `rules{year}.php` static rulebook pages and `RulesSup*.php` — Phase 14.
- Cross-reference / supersession links between issues (roadmap item 5).
- Role-label sponsors in general (roadmap item 5) — **except** the specific
  `Commissioner` → Josh Utterback mapping handled in the backfill (decision 2,
  step 0).
- Rewriting historical **ballot** vote data — only FK constraints are added;
  existing rows untouched.

## Affected areas

- `symfony-app/migrations/` — one new migration (schema + FKs).
- `symfony-app/src/Entity/` — `Issue`, `IssueSponsor` entities; `Ballot`
  entity (new or updated) for the FK mappings.
- `symfony-app/src/Repository/` — issue/ballot queries.
- `symfony-app/src/Service/` — `MarkdownService`, proposal-list/ballot
  services, `MarkdownExtension` Twig filter.
- `symfony-app/src/Controller/` — member proposals/submit/ballot controllers;
  admin proposal controller.
- `symfony-app/templates/` — proposals list, submit, ballot, admin.
- `scripts/` — one-time backfill parser producing reviewable SQL + a report.
- `football/rules/` — proposal/ballot/propose pages deleted with 301s
  (`LegacyRulesRedirectController` or similar); `rules{year}.php` stay.
- `scripts/database/schema.sql` — regenerated.
