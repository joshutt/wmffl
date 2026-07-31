# Requirements — Comment-count badge + login-modal buttons (Phase 11, items 3 & 4)

## Scope

The last two items in `specs/roadmap.md` Phase 11 ("Small fixes"). Independent
of each other but small enough to land together as one PR on
`phase11-proposals-formatting`, continuing after items 1–2
(`specs/2026-07-31-proposals-formatting/`).

1. **Article cards — comment count indicator.** `templates/article/_card.html.twig`
   (used by `article/list.html.twig` and `home/index.html.twig`) shows
   title/date/author only, no hint of discussion activity. Add a comment-count
   badge sourced from `Comment` (`active = 1` rows only).

2. **Login form alongside "must be logged in" messages.** Ten gated pages show
   a plain logged-out message with no way to log in right there, even though a
   global login modal (`#loginModal`, `templates/base.html.twig:88-117`,
   opened today only by the navbar "Log In" button at `base.html.twig:83`)
   already exists on every page. Add a "Log In" button next to each message
   that opens the same modal, via one shared partial.

## Decisions

### Item 3 — comment count

- **Query**: add `CommentRepository::countByArticleIds(array $articleIds):
  array<int,int>` — one batch query (`GROUP BY article_id`, `WHERE active =
  1`), returning `[articleId => count]`, keyed only for articles that have at
  least one active comment (missing key = 0). Avoids N+1 across a page of
  cards. No new single-article `countByArticle` method — nothing calls
  `_card.html.twig` for a single article outside a list context.
- **Wiring**: `ArticleController::list` and `HomeController::index` each call
  `countByArticleIds()` once with the ids of the articles they're about to
  render, and add the resulting map to their `render()` array as `counts`.
  `_card.html.twig` reads `counts[article.id] ?? 0` directly — it relies on
  Twig's `include()` inheriting the calling template's full context by
  default (already the pattern `home/index.html.twig` uses for its bare
  `include('article/_card.html.twig')` calls in the loop), so neither
  template's existing `include()` call sites need to change.
- **Badge markup**: a small Bootstrap 4 badge (`<span class="badge
  badge-secondary">💬 {{ count }}</span>`, matching the `badge-secondary`/
  `badge-warning`/`badge-success` usage already in
  `templates/admin/proposals/index.html.twig`), placed in the card body near
  the date/author line.
- **Zero comments**: badge always shows, including "💬 0" — every card gets
  a consistent discussion-activity indicator regardless of count.
- **No entity/migration changes.** `Comment.articleId` is a plain int column
  already; the new repository method is a read-only aggregate query.

### Item 4 — login button next to gated messages

- **Shared partial**: new `templates/_login_required.html.twig`, taking a
  `message` variable. Renders a standardized block: the message text plus a
  `btn-wmffl` "Log In" button with `data-toggle="modal" data-target=
  "#loginModal"` (identical trigger attributes to the navbar button at
  `base.html.twig:83`, so no JS changes are needed — the existing modal
  markup and `submitContactForm()` handler in `base.html.twig` are reused
  as-is).
- **Standardize appearance**: all ten call sites render through the same
  partial markup (`text-center font-weight-bold h4` message + centered
  button below it), replacing whatever ad hoc wrapper (`div`/`p`,
  with/without `font-weight-bold`/`h4`) each page uses today. This is a
  deliberate small visual convergence, not just a button bolt-on.
- **Call sites** (10): each keeps its own existing logged-out condition
  (`{% if not loggedIn %}`, `{% if not isLoggedIn %}`,
  `{% if eligible is null %}` in `ir.html.twig`) and only the *contents* of
  that branch change to `{{ include('_login_required.html.twig', {message:
  '...'}) }}`:
  - `templates/transactions/protections_saved.html.twig:10-13` (`error ==
    'not_logged_in'` branch — the other three `error` branches
    (`deadline`/`unpaid`/`over_budget`) are untouched, they stay as their own
    `alert-secondary` boxes since those aren't login-related)
  - `templates/transactions/protections.html.twig:9-10`
  - `templates/transactions/ir.html.twig:15-16`
  - `templates/transactions/list.html.twig:11-12`
  - `templates/transactions/confirm.html.twig:10-11`
  - `templates/draftdate/index.html.twig:9-10`
  - `templates/proposals/ballot.html.twig:9-10`
  - `templates/proposals/submit.html.twig:16-17`
  - `templates/trades/index.html.twig:11-12`
  - `templates/article/publish.html.twig:15-16`
- **`ArticleController::addComment` flash** (`src/Controller/ArticleController.php:83`,
  actually the `comment()` action): verification only, no code change
  expected. The "You must be logged in to comment" flash renders via the
  generic `app.flashes('error')` loop already in
  `templates/article/view.html.twig:7-9` (a shared `alert-danger` box used
  for several error flashes on that page, not login-specific — it should
  NOT be swapped for the new partial). The redirect target
  (`article_view`) is a normal page with the navbar's own "Log In" button
  already visible, so the trigger requirement is already met; confirm this
  during manual verification rather than changing code.

## Out of scope

- Phase 12+ items (quicklinks drag-and-drop, admin config editor, error
  handling, activations UI, etc.) — separate phases.
- Any change to `Comment`/`CommentRepository`'s existing methods
  (`findActiveByArticle`, `findThreadByArticle`, `findPageForAdmin`).
- Any change to the login modal's own markup or `submitContactForm()` JS in
  `base.html.twig` — reused as-is.
- Replacing the generic error-flash box on `article/view.html.twig` with the
  new login partial (see decision above).

## Context

- Branch: `phase11-proposals-formatting` (continuing after
  `specs/2026-07-31-proposals-formatting/`)
- Relevant files:
  - `symfony-app/src/Repository/CommentRepository.php`
  - `symfony-app/src/Controller/ArticleController.php`
  - `symfony-app/src/Controller/HomeController.php`
  - `symfony-app/templates/article/_card.html.twig`
  - `symfony-app/templates/_login_required.html.twig` (new)
  - The ten templates listed above
  - `symfony-app/tests/Repository/CommentRepositoryTest.php`
  - `symfony-app/tests/Controller/ArticleControllerTest.php`
  - `symfony-app/tests/Controller/HomeControllerTest.php`
