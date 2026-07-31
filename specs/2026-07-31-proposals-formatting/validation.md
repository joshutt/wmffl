# Validation — Proposals formatting fixes

Implementation is done and mergeable when all of the following hold.

## Automated tests

- [ ] `cd symfony-app && vendor/bin/phpunit tests/` passes in full, no
      regressions (baseline: 781 tests green as of Phase 10.6).
- [ ] New `MarkdownServiceTest` cases pass:
  - single newline → `<br>` (soft break no longer collapses to a space)
  - leading spaces → `&nbsp;`-preserved indentation, no `<pre><code>`
    block
  - leading tab → four `&nbsp;`s
  - hard-break-style input (trailing double-space, matching
    `ProposalPageParser` output) still renders `<br>` — unaffected by the
    soft_break change
  - existing nested-blockquote/list test still passes unchanged
- [ ] New/updated `ProposalControllerTest` (or equivalent) cases pass:
  - `/rules/proposals` list shows rationale text, not the short
    description, for a fixture issue with both set
  - `/rules/ballot` shows only the short description, not
    rationale/rule-change text

## Manual verification

- [ ] On `/rules/proposals/submit`, typing a multi-line rationale with an
      indented sub-item shows matching line breaks and indentation in the
      live preview.
- [ ] Submitting that proposal (or an admin edit via `/admin/proposals`)
      and viewing it on `/rules/proposals` shows the same line breaks and
      indentation as the preview.
- [ ] The imported 2025.1 proposal content (from
      `specs/2026-07-27-rule-proposals-issues/`) on `/rules/proposals`
      renders identically to its pre-change appearance — no regression
      from the `soft_break`/indentation change for hard-break-style
      imported content.
- [ ] `/rules/proposals` no longer shows the short `Description` field
      anywhere; `/rules/ballot` shows only the short `Description` and
      nothing else per item.

## Not required for merge

- No migration to run (no schema change).
- No deploy-time data fix needed (template/service-only change).
