# Plan — Articles: read-only display + admin tooling

Task groups in order. Each group should leave the app working (Symfony tests
green, legacy publish flow still reachable).

## 1. ArticleRepository

- Create `symfony-app/src/Repository/ArticleRepository.php` (constructor-
  injected `EntityManagerInterface`, DQL — mirrors the legacy
  `articleUtils.php` queries):
  - `find(int $id): ?Article` — any article, active or not.
  - `findLatestActive(): ?Article` — `active = 1`, `displayDate DESC,
    priority DESC`, max 1.
  - `findActivePage(int $limit, int $page = 0): array` — `active = 1`,
    join + select author, `displayDate DESC, priority ASC`, offset
    `page * $limit`.
  - `findAllForAdmin(): array` — all articles (incl. inactive),
    `displayDate DESC`, join author.
- Unit tests in `symfony-app/tests/` per existing conventions.

## 2. Article view page

- `ArticleController` with route `/article/{id<\d+>}` (name `article_view`):
  fetch via repository, 404 on unknown id, render
  `templates/article/view.html.twig` — a straight port of `view-snip.php`
  (title, figure/caption, published date `M d, Y`, optional byline, raw
  article text via `|raw`).
- Route `/article` (no id): render the latest active article (legacy
  `view.php` behavior with no `uid`).
- Redirect route for `/article/view`: 301 to `article_view` with
  `id = uid` query param, or to `/article` when `uid` is absent/empty.

## 3. Article list page

- Shared card partial `templates/article/_card.html.twig` (port of
  `printArticleCard()`: image, title, date, author, links to
  `article_view`).
- Route `/articles` (name `article_list`), `?page=N` (default 0), 24 per
  page: card grid with the legacy responsive wrap behavior, Older/Newer
  pagination buttons (`btn-wmffl`; hide Newer on page 0), and a
  "Write Article" button linking to legacy `/article/publish` when
  `AuthenticationService` says the user is logged in.
- Redirect route for `/article/list?start=N`: 301 to `/articles?page=N`.
- Update the News link in `football/base/menu.php` from `/article/list`
  to `/articles`.

## 4. Homepage

- `ScoresRepository` (DBAL `Connection`, style of `StandingsRepository`):
  port the two queries from `football/scores.php` — latest visible week
  from `weekmap` (12-hour display-date offset, week-1 floor, previous
  season when `currentWeek < 1`), then that week's games with
  leader/trailer names+scores, label, overtime flag.
- `HomeController` at `/` (name `home`) rendering
  `templates/home/index.html.twig`, porting the `football/index.php`
  layout:
  - Article block: top 4 active articles — first as the main card, next 3
    in a card deck — reusing `article/_card.html.twig`.
  - Scores/Standings tab card: `home/_scores.html.twig` (port of
    `scores.php` markup, per-game links preserved) and
    `home/_standings.html.twig` (port of `standings.php`, grouped by
    division with short records) fed by `StandingsRepository` +
    `StandingsCalculatorService` with the week-0 fallback.
  - Trash-talk card: `home/_trashtalk.html.twig`, 6 latest `Forum` posts
    (query can live in the controller or a small repo method).
  - Quicklinks card: `home/_quicklinks.html.twig`, static links with the
    season year from `SeasonWeekService`.
  - Keep the `index.css` / `front.js` asset includes and Bootstrap tab
    markup working (assets still served via legacy fallback).

## 5. Admin article management

- `AdminArticleController` extending `AbstractAdminController`, all
  actions gated by `requireCommissioner()`:
  - `GET /admin/articles` — table of all articles (title, author, display
    date, priority, active), newest first, with edit links and
    activate/deactivate toggles.
  - `GET/POST /admin/articles/new` and `GET/POST /admin/articles/{id}/edit`
    — form for title, caption, image link (path string — no upload in this
    phase), text (textarea), display date, priority, active, and author
    (select of users). Edit works on **any** article — commissioner access
    is not limited by authorship or active state.
  - `POST /admin/articles/{id}/toggle` — flip `active`, redirect back to
    the list.
- Templates under `templates/admin/article/`; buttons `btn-wmffl` in
  `text-center` wrappers, consistent with the other admin pages.
- Add an Articles link to the admin dashboard.

## 6. Legacy cleanup + roadmap

- Delete: `football/article.php` (homepage block),
  `football/article/view.php`, `football/article/list.php`,
  `football/article/articleDisplay.php`, and `football/index.php` (now
  served by `HomeController`; confirm LegacyBridge doesn't special-case it).
- Keep (publish flow still includes them): `football/article/article.php`,
  `articleUtils.php`, `view-snip.php`, plus `process.php`, `publish.php`,
  `preview.php`, `confirm.php`.
- Verify `/article/publish` → preview → confirm still works through the
  LegacyBridge after deletions.
- Update `specs/roadmap.md`: mark Phase 1 step 1 (+ homepage + admin
  tooling) done.

## 7. Validation pass

- Run the full checklist in validation.md (automated + manual parity);
  fix anything it surfaces before opening the PR.
