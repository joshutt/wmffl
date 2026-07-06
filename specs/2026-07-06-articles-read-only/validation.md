# Validation — Articles: read-only display + admin tooling

The branch is mergeable when everything below passes.

## Automated tests

Run from `symfony-app/`: `vendor/bin/phpunit tests/` — all green, including
new tests. (If coverage is requested, use `--coverage-clover coverage.xml`.)

New tests follow the existing mock-based `TestCase` conventions
(see `tests/Controller/AdminMoneyControllerTest.php`):

- **ArticleRepository** — query-building behavior for the four methods
  (limits, offsets, active filter, ordering).
- **ArticleController** — view renders the right template with the right
  article; unknown id → 404; `/article` with no id uses the latest active
  article; legacy `/article/view?uid=N` and `/article/list?start=N` return
  301 to the new URLs (including the no-`uid` case).
- **HomeController** — renders the homepage template with articles, scores,
  standings, forum posts; week-0 fallback feeds previous season/week 16 to
  the standings widget.
- **AdminArticleController** — every action redirects non-commissioners
  to `/`; list renders all articles including inactive; create/edit
  persist the submitted fields; toggle flips `active` and redirects.

## Manual parity (real DB, side by side with legacy)

Before the legacy files are deleted (task group 6), compare Symfony output
against the live legacy pages:

- [ ] `/article/{id}` matches legacy `/article/view?uid={id}` for a recent
      article: title, image + caption, published date, byline, body HTML
      renders (not escaped).
- [ ] A direct link to an **inactive** article still renders (legacy
      behavior).
- [ ] `/articles` matches legacy `/article/list`: 24 cards, same order,
      images render, Older/Newer paging works at page 0, 1, and the last
      page; "Write Article" appears only when logged in and reaches the
      legacy publish page.
- [ ] `/` matches legacy homepage: main article card + 3-card deck, scores
      tab (current week, labels, OT indicator, per-game links), standings
      tab (divisions, short records, team links), trash-talk list (6 posts,
      links to forum), quicklinks (correct current-season URLs).
- [ ] Article images load on all three pages (served via legacy fallback).
- [ ] Legacy menu "News" link goes to `/articles`.

## Admin tooling

- [ ] Non-commissioner hitting any `/admin/articles*` URL is redirected
      to `/`.
- [ ] Commissioner can: see all articles (inactive visibly flagged), create
      an article that then appears on `/articles` and the homepage,
      edit every field of any existing article — including one authored by
      a different user and one that is inactive — and deactivate an article
      (it disappears from `/articles` and homepage but its direct URL still
      renders).

## Regression (post-cleanup)

- [ ] After deleting the legacy files: `/article/publish` → preview →
      confirm flow still works end to end through the LegacyBridge
      (it includes the retained `article/article.php` / `articleUtils.php`
      / `view-snip.php`).
- [ ] A handful of untouched legacy routes (e.g. `/teams/roster`,
      `/transactions/list`) still load — LegacyBridge fallback intact.
- [ ] `/standings` and `/history/standings/{season}/{week}` unaffected by
      the standings-widget reuse.

## Merge gate

All automated tests green, every checklist box above ticked, and the PR is
scoped to this phase only (no publish-flow migration snuck in).
