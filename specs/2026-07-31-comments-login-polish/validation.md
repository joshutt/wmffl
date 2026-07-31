# Validation — Comment-count badge + login-modal buttons

Implementation is done and mergeable when all of the following hold.

## Automated tests

- [ ] `cd symfony-app && vendor/bin/phpunit tests/` passes in full, no
      regressions (baseline: 793 tests green as of Phase 11 items 1–2).
- [ ] `CommentRepositoryTest::testCountByArticleIdsCountsOnlyActiveComments`
      (or equivalent) passes: only `active = 1` comments counted, articles
      with zero active comments absent from the returned map.
- [ ] `ArticleControllerTest` and `HomeControllerTest` cases pass: rendered
      `/articles` and `/` pages show a comment-count badge with the correct
      number for an article with active comments, and a "💬 0" badge for an
      article with zero.
- [ ] Each of the ten controller test files listed in `plan.md` §7 has a
      logged-out-state test asserting the response contains the login
      trigger (`data-target="#loginModal"` or equivalent), not just the
      bare message text.

## Manual verification

- [ ] `/articles` (list page) and `/` (homepage) show correct, matching
      comment-count badges per article, including "💬 0" for zero-comment
      articles.
- [ ] Each of the ten gated pages, visited while logged out, shows its
      message with a "Log In" button next to it; clicking the button opens
      the same modal as the navbar's "Log In" button (no separate modal
      instance, no JS console errors).
- [ ] Logging in via that modal from one of the ten gated pages behaves the
      same as logging in via the navbar button (page reloads showing the
      now-logged-in content) — confirms no regression to
      `submitContactForm()`.
- [ ] `protections_saved.html.twig`'s other three error states (`deadline`,
      `unpaid`, `over_budget`) still render their original `alert-secondary`
      boxes unchanged — only the `not_logged_in` case changed.
- [ ] `article/view.html.twig`'s "You must be logged in to comment" flash
      (triggered by posting a comment while logged out) still renders in
      its existing generic error-flash box, with the navbar's "Log In"
      button visibly available on the same page — confirmed as sufficient,
      no code change made there.

## Not required for merge

- No migration to run (no schema change — `Comment.articleId` unchanged).
- No deploy-time data fix needed (repository/controller/template-only
  change).
