# Plan — Proposals formatting fixes

## 1. `MarkdownService` — soft breaks + indentation

- In `MarkdownService::__construct`, add `'renderer' => ['soft_break' =>
  "<br>\n"]` to the `CommonMarkConverter` config array (alongside the
  existing `html_input`/`allow_unsafe_links` keys).
- Add a private normalization step in `toHtml()`, run on the input before
  `$this->converter->convert()`:
  - Split on `\n`.
  - For each line, replace leading run of spaces/tabs with `&nbsp;` per
    space and `&nbsp;&nbsp;&nbsp;&nbsp;` per tab, preserving order (e.g. a
    line starting with `\t  ` becomes 4+2 = 6 `&nbsp;`s), then keep the
    rest of the line unchanged.
  - Rejoin with `\n`.
  - This must run on the *raw Markdown* (not HTML) since `html_input =>
    escape` will otherwise escape a literal `&nbsp;` — confirm behavior
    with a test; CommonMark's HTML-entity handling of raw `&nbsp;` in
    Markdown source needs verification (test #1 below) since
    `html_input => escape` escapes actual `<`/`>`/HTML tags but is not
    expected to affect a bare entity reference — check the rendered output
    is `&nbsp;` → U+00A0 in the browser, not a literal escaped ampersand.
    If `escape` mode does mangle the entity, use the same placeholder
    trick `ProposalPageParser::toMarkdown()` uses (swap `&nbsp;` for a
    plain-text token, convert, swap back) instead of emitting `&nbsp;`
    pre-conversion.

## 2. `MarkdownService` tests

Add to `MarkdownServiceTest`:
- `testSingleNewlineRendersAsLineBreak` — two-line input with a single
  `\n` between them renders with a `<br>` between the two lines' content
  (not collapsed to a space).
- `testLeadingSpacesPreserveIndentation` — a line with leading spaces
  (e.g. `"  nested item"`) renders with `&nbsp;` (not a `<pre><code>`
  block, confirming the 4-space indented-code-block trap is avoided).
- `testLeadingTabPreservesIndentation` — a leading tab renders as four
  `&nbsp;`s.
- `testImportedHardBreakStyleUnaffected` — feed in a hard-break-style
  input (`"line one  \nline two"`, i.e. trailing double-space before the
  newline, matching what `ProposalPageParser` produces) and confirm it
  still renders `<br>` between the lines (regression guard: this already
  passed before the change since hard breaks are a Markdown default,
  confirm the new soft_break option doesn't double up or otherwise change
  that output).
- Keep all existing tests green, particularly
  `testMultiLevelBlockquoteRendersNestedQuotesWithoutRawHtml` (nested
  blockquotes/lists must still render correctly with the new options).

## 3. Client-side preview parity (`submit.html.twig`)

- Update `miniMarkdown()` in the `<script>` block:
  - Add a leading-whitespace-to-`&nbsp;`-run step per line (mirroring the
    server), applied after `escapeHtml()` but before the existing
    bold/italic/link/list/blockquote regex passes — same
    space-per-space/tab-as-four-spaces rule as the server.
  - Existing per-line `<p>` wrapping already produces one visual break per
    typed line, which is the visual behavior the server-side `soft_break`
    change is matching — no change needed there, just confirm by manual
    test in step 5.

## 4. `list.html.twig` — show Rationale, not Description

- In `symfony-app/templates/proposals/list.html.twig`, delete the block:
  ```twig
  {% if issue.description %}
      <p>{{ issue.description }}</p>
  {% endif %}
  ```
- Leave the `rationale` and `ruleChangeText` blocks immediately below it
  unchanged.

## 5. `ballot.html.twig` — confirm Description-only (no code change expected)

- Read `templates/proposals/ballot.html.twig` and confirm the card body
  shows only `issue.description`, with no `rationale`/`ruleChangeText`
  reference. It already does as of Phase 10.6 — this step is a
  verification, not an edit. If it turns out to show more than
  `description`, trim it down to match.

## 6. Controller test coverage

In `symfony-app/tests/Controller/ProposalControllerTest.php` (or a new
test if the existing file doesn't cover rendering assertions well), add:
- A proposals-list test asserting the rendered page contains the
  rationale text and does NOT contain the raw description text for a
  fixture issue that has both fields set differently.
- A ballot test (if not already covered) asserting the ballot page shows
  `description` and does not contain rationale/rule-change text for a
  fixture issue that has both.

## 7. Manual verification

- Run the dev server (`symfony-app/bin/console server:start` or
  equivalent per `run` skill), log in as a member, visit
  `/rules/proposals/submit`, type a rationale with multiple lines and an
  indented sub-item, confirm the live preview shows line breaks and
  indentation matching the eventual saved/rendered output.
- Visit `/rules/proposals` for a season containing the imported 2025.1
  content (per `specs/2026-07-27-rule-proposals-issues/`) and confirm it
  renders identically to before this change (screenshot or manual diff of
  the relevant card).
- Visit `/rules/ballot` and confirm cards show only the short description.

## 8. Run full test suite

- `cd symfony-app && vendor/bin/phpunit tests/` — confirm all tests pass,
  including the new ones, with no regressions in the 781-test baseline
  noted in `specs/2026-07-27-rule-proposals-issues/`.
