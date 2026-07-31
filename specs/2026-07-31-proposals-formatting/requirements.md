# Requirements — Proposals formatting fixes (Phase 11, items 1 & 2)

## Scope

Two small, self-contained fixes from `specs/roadmap.md` Phase 11 ("Small
fixes"), both touching the rule-proposals feature landed in Phase 10.6.
Land together as one PR.

1. **Proposal Markdown authoring — line breaks and indentation.**
   `MarkdownService` (`symfony-app/src/Service/MarkdownService.php`) renders
   member/admin-authored `Rationale` and `RuleChangeText` with CommonMark
   defaults, which collapse a single typed newline into a soft break (a
   space) and drop leading-space indentation. The historical proposals
   imported by `app:backfill-proposals` don't hit this problem — the
   importer (`ProposalPageParser`) already emits proper Markdown hard
   breaks (`"  \n"`, i.e. trailing double-space) for `<br>` and `&nbsp;`
   runs for indentation, both of which CommonMark already renders
   correctly. But proposals *typed directly* into the submit/admin forms
   have no such transformation — a member pressing Enter between lines
   gets them squashed together on the rendered page, which doesn't match
   how the imported (Phase 10.6) content displays.

2. **Proposal vs. ballot field visibility.**
   `templates/proposals/list.html.twig` and `templates/proposals/ballot.html.twig`
   currently overlap in what they show. Split them:
   - `list.html.twig` should show `Rationale`, not the short `Description`.
   - `ballot.html.twig` already shows only `Description` (no rationale/rule
     change) — no code change needed there, just confirm via a test.

## Decisions

- **soft_break config**: set CommonMark's `renderer.soft_break` option to
  `"<br>\n"` so every single `\n` becomes a line break. This changes
  soft-break rendering only; existing imported content uses hard breaks
  (two trailing spaces) which render as `<br>` under CommonMark defaults
  already, so this option is additive and shouldn't change imported output.
- **Indentation**: normalize leading whitespace on each line to `&nbsp;`
  before handing text to the converter (space → `&nbsp;`, tab → four
  `&nbsp;`s), matching the encoding already used by the backfill importer
  (`ProposalPageParser::toMarkdown`) so both paths produce the same kind of
  indentation-preserving HTML. This also sidesteps CommonMark's 4-space
  indented-code-block rule, which would otherwise turn indented text into a
  `<pre><code>` block instead of a plain indented paragraph.
- **Where this applies**: only `MarkdownService::toHtml()` — it renders
  exactly two fields (`Rationale`, `RuleChangeText`) on exactly two
  surfaces (member submit form preview via `/rules/proposals/submit`,
  admin edit form via `/admin/proposals`), both flowing through
  `markdown_to_html` on `list.html.twig`. Blast radius is contained to
  that one service; no other Markdown rendering exists in the app.
- **Client-side preview**: `templates/proposals/submit.html.twig`'s
  `miniMarkdown()` JS must match the new server behavior — currently it
  turns every line into its own `<p>`, which already behaves like a hard
  line break, but the indentation stripping needs a matching change (see
  plan) so what the member sees while typing matches what's saved.
- **list.html.twig change**: drop the
  `{% if issue.description %}<p>{{ issue.description }}</p>{% endif %}`
  block; keep the existing `rationale` and `ruleChangeText` blocks as-is.
- **No entity/migration changes.** Both fixes are template/service-level;
  `Issue` entity fields (`description`, `rationale`, `ruleChangeText`) are
  unchanged.

## Out of scope

- Phase 11 items 3 (article comment-count badge) and 4 (login-modal
  buttons) — deferred to a separate spec/PR per the scoping decision for
  this branch.
- Any change to the admin proposal form's lack of a live preview (it has
  none today, and none is being added).
- Any change to how the backfill importer (`ProposalPageParser`) encodes
  Markdown — it's already correct for this problem and is out of scope.

## Context

- Branch: `phase11-proposals-formatting`
- Relevant files:
  - `symfony-app/src/Service/MarkdownService.php`
  - `symfony-app/tests/Service/MarkdownServiceTest.php`
  - `symfony-app/templates/proposals/list.html.twig`
  - `symfony-app/templates/proposals/ballot.html.twig`
  - `symfony-app/templates/proposals/submit.html.twig` (client preview JS)
  - `symfony-app/tests/Controller/ProposalControllerTest.php`
