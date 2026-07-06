# Requirements — Articles: read-only display + admin tooling

Roadmap Phase 1, step 1, plus admin article management (per mission.md's
"admin tools in step" principle). Branch: `articles-read-only`.

## Scope

Migrate the read-only article surfaces from legacy PHP to Symfony, and add
admin tooling so commissioners can manage articles without raw DB access:

1. **Article view page** — replaces `football/article/view.php` +
   `view-snip.php` rendering.
2. **Article list page** — replaces `football/article/list.php`.
3. **Homepage** — replaces `football/index.php` entirely, including the
   article-card block (`football/article.php`) and the sidebar widgets it
   includes (scores, standings, trash-talk comment list, quicklinks).
4. **Admin article management** — new (no legacy equivalent): list all
   articles including inactive, create, edit **any** article (regardless of
   author, active or inactive), activate/deactivate.

## Out of scope (deferred to later Phase 1 steps)

- The member-facing authoring/publishing flow: `process.php`, `publish.php`,
  `preview.php`, `confirm.php` (Phase 1 step 2). These stay legacy and must
  keep working via the LegacyBridge after this phase.
- Article comments (Phase 1 step 3). `article/article.php` has a
  `printComment()` helper, but it is dead/half-migrated code
  (`$comment->getLink('author_id')` is not the Doctrine API) and the routed
  view page never renders comments. Do not port.

## Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Phase scope | Step 1 read-only + admin tooling | Small PR per roadmap; mission.md requires admin tools to ship with the feature area. Member publish flow deferred. |
| URL scheme | Clean URLs with 301 redirects | New routes `/article/{id}` and `/articles`; legacy paths `/article/view?uid=N` and `/article/list?start=N` 301-redirect to them. Legacy menu link updated to `/articles`. |
| Homepage | Migrate whole homepage | `football/index.php` moves to a Symfony `HomeController` at `/`. Sidebar widgets are small (159 lines total across 4 files) and get ported as Twig partials. |
| Validation | Tests + manual parity | PHPUnit tests per existing conventions, plus manual side-by-side comparison against the legacy pages with real DB data. See validation.md. |

## Context

- `App\Entity\Article` **already exists** (`symfony-app/src/Entity/Article.php`,
  table `articles`, `ManyToOne` author → `User`). The legacy article files
  were already half-migrated to use it through the shared EntityManager. No
  entity work is needed beyond what the admin form requires.
- Legacy query semantics to preserve (from `football/article/articleUtils.php`):
  - Single article: by id (any, active or not — direct links to inactive
    articles render today; keep that behavior); with no id, the most recent
    `active = 1` article by `displayDate DESC, priority DESC`.
  - Lists: `active = 1`, ordered `displayDate DESC, priority ASC`, joined to
    author. Homepage takes 4 (first is the "main" card); list page takes 24
    per page with `start` (page index) offset = `start * 24`.
- The list page shows a "Write Article" button to logged-in members
  (legacy `$isin`). Keep it, pointing at the legacy `/article/publish` route
  until step 2 migrates it. Use `AuthenticationService` for the check.
- Standings sidebar widget: reuse `StandingsRepository` +
  `StandingsCalculatorService` (same code path as `StandingsController`,
  including the week-0 → previous-season/week-16 fallback).
- Scores sidebar widget: port the SQL from `football/scores.php` (weekmap
  "latest visible week" subquery + schedule/team join) into a DBAL-based
  repository, following the `StandingsRepository` style.
- Trash-talk widget: 6 most recent `App\Entity\Forum` rows by `createTime`
  (from `football/forum/commentlist.php`).
- Quicklinks widget: static markup (`football/quicklinks.php`, 9 lines) with
  hardcoded current-season URLs; build the season year from
  `SeasonWeekService` instead of hardcoding 2026.
- Admin controllers extend `AbstractAdminController` and gate with
  `requireCommissioner(AuthenticationService)`; templates live under
  `templates/admin/<feature>/`; buttons use `btn-wmffl` inside a
  `text-center` wrapper.
- **Cleanup constraint:** the deferred publish flow includes
  `article/article.php` (via `preview.php`), which requires
  `articleUtils.php` and `view-snip.php`. Those three files must NOT be
  deleted this phase, even though the routed read-only pages stop using them.

## Risks / notes

- Article images: `Article.link` holds a root-relative image path rendered
  as `<img src="/{link}">`. Those assets are served by the legacy side
  (LegacyBridge fallback / `img.php`). Nothing changes here, but manual
  validation must confirm images render on the Symfony-served pages.
- Taking over `/` is the highest-traffic route change in the migration so
  far; the homepage parity check in validation.md is the main merge gate.
- `football/article/articleDisplay.php` is a debug script that dumps article
  225 — delete it; nothing includes it.
