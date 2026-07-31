# Plan — Comment-count badge + login-modal buttons

## 1. `CommentRepository::countByArticleIds()`

- Add to `symfony-app/src/Repository/CommentRepository.php`:
  ```php
  /**
   * Active comment counts for a set of articles, keyed by article id.
   * Articles with no active comments are absent from the result (caller
   * treats a missing key as 0).
   *
   * @param int[] $articleIds
   * @return array<int,int>
   */
  public function countByArticleIds(array $articleIds): array
  {
      if ($articleIds === []) {
          return [];
      }

      $rows = $this->em->createQuery(
          'SELECT c.articleId AS articleId, COUNT(c.id) AS cnt FROM App\Entity\Comment c
           WHERE c.articleId IN (:ids) AND c.active = 1 GROUP BY c.articleId'
      )->setParameter('ids', $articleIds)->getResult();

      $counts = [];
      foreach ($rows as $row) {
          $counts[(int) $row['articleId']] = (int) $row['cnt'];
      }

      return $counts;
  }
  ```
- Empty-array short-circuit avoids an `IN ()` DQL error when a page has zero
  articles.

## 2. Wire counts into `ArticleController::list` and `HomeController::index`

- `ArticleController::list` (`src/Controller/ArticleController.php`): after
  fetching `$articles = $articles->findActivePage(...)`, compute
  `$counts = $comments->countByArticleIds(array_map(fn($a) => $a->getId(),
  $articles))` and add `'counts' => $counts` to the `render()` array.
  `CommentRepository` needs to be added as a controller argument (it's
  already injected on `view`/`latest`/`comment`, just not `list`).
- `HomeController::index` (`src/Controller/HomeController.php`): same
  pattern — inject `CommentRepository`, compute counts for
  `$articleRepository->findActivePage(4)`'s ids, add `'counts' => $counts`
  to the render array.

## 3. `_card.html.twig` badge

- In `templates/article/_card.html.twig`, inside `.card-body` near the
  date/author `<p>`, add:
  ```twig
  {% set commentCount = counts[article.id] ?? 0 %}
  <span class="badge badge-secondary">💬 {{ commentCount }}</span>
  ```
  Relies on `counts` being present in whichever parent template's context
  the include runs in (`list.html.twig` loop context, or
  `home/index.html.twig`'s two include call sites) — no change needed to
  either template's `include()` calls themselves, since Twig `include()`
  inherits the calling template's full context by default.

## 4. Repository + controller tests

- `tests/Repository/CommentRepositoryTest.php`: add
  `testCountByArticleIdsCountsOnlyActiveComments` — fixture with one article
  having 2 active + 1 inactive comment and another article with 0 comments;
  assert the result map has the first article's id → 2 and the second is
  absent (or maps to 0 via `?? 0` at the call site).
- `tests/Controller/ArticleControllerTest.php`: extend (or add) a
  list-page test asserting the rendered page contains a `badge` with the
  expected count for a fixture article with active comments, and also
  shows a "💬 0" badge for a fixture article with zero active comments.
- `tests/Controller/HomeControllerTest.php`: same style assertion for the
  homepage's article cards.

## 5. `_login_required.html.twig` partial

- New `symfony-app/templates/_login_required.html.twig`:
  ```twig
  <div class="text-center font-weight-bold h4 my-3">
      {{ message }}
      <div class="mt-2">
          <button type="button" class="btn btn-wmffl" data-toggle="modal" data-target="#loginModal">Log In</button>
      </div>
  </div>
  ```
  Same `data-toggle`/`data-target` pair as the navbar button
  (`base.html.twig:83`) — no JS or modal-markup changes needed.

## 6. Swap in the ten call sites

Each site's existing logged-out condition is unchanged; only the branch
contents change to an include of the new partial with that page's existing
message text:

- `templates/transactions/protections_saved.html.twig:10-13` — only the
  `error == 'not_logged_in'` branch changes (leave the `deadline`/`unpaid`/
  `over_budget` `alert-secondary` branches as-is):
  ```twig
  {% if error == 'not_logged_in' %}
      {{ include('_login_required.html.twig', {message: 'You must be logged in to save protections.'}) }}
  ```
- `templates/transactions/protections.html.twig:9-10` — message "You must be
  logged in to submit protections"
- `templates/transactions/ir.html.twig:15-16` — condition stays
  `{% if eligible is null %}`; message "You must be logged in to use this
  feature"
- `templates/transactions/list.html.twig:11-12` — message "You must be
  logged in to use this feature"
- `templates/transactions/confirm.html.twig:10-11` — message "You must be
  logged in to perform transactions"
- `templates/draftdate/index.html.twig:9-10` — message "You must be logged
  in to use this feature"
- `templates/proposals/ballot.html.twig:9-10` — message "You must be logged
  in to cast your votes."
- `templates/proposals/submit.html.twig:16-17` — message "You must be
  logged in to submit rule proposals."
- `templates/trades/index.html.twig:11-12` — message "You must be logged in
  to use this feature"
- `templates/article/publish.html.twig:15-16` — message "You must be logged
  in to use this feature"

## 7. Controller test coverage for the button

For each of the ten pages, find the existing logged-out-state test in its
controller's test file (most already assert the plain message text) and
extend the assertion to also check for the login-trigger markup (e.g.
`data-target="#loginModal"` or `btn-wmffl` + `Log In` inside the response
body). Where a controller test file has no logged-out case today, add one.
Controllers/tests to touch:
- `ProtectionsController` → `tests/Controller/ProtectionsControllerTest.php`
  (covers both `protections.html.twig` and `protections_saved.html.twig`)
- `InjuredReserveController` → `tests/Controller/InjuredReserveControllerTest.php`
- `RosterMoveController` → `tests/Controller/RosterMoveControllerTest.php`
  (covers both `list.html.twig` and `confirm.html.twig`)
- `DraftDateController` → `tests/Controller/DraftDateControllerTest.php`
  (new file if none exists for the member-facing controller — only the
  admin variant has a test today)
- `BallotController` → add/extend in a `BallotControllerTest.php` if one
  doesn't already cover the logged-out case, else extend
  `ProposalControllerTest.php` if ballot assertions live there
- `ProposalController` (`/rules/proposals/submit`) →
  `tests/Controller/ProposalControllerTest.php`
- `TradeController` → `tests/Controller/TradeControllerTest.php`
- `ArticlePublishController` → `tests/Controller/ArticlePublishControllerTest.php`

## 8. Manual verification

- Visit `/articles` and `/` logged out and logged in; confirm badges show
  the right counts (cross-check against `/article/{id}`'s visible comment
  thread count) and are absent on articles with zero active comments.
- Visit each of the ten gated pages logged out; confirm the message +
  "Log In" button render, clicking the button opens the same modal as the
  navbar button, and a successful login reloads into the originally
  intended page (existing `submitContactForm()` behavior — confirm it's
  unaffected).
- Trigger `ArticleController::comment` logged out (POST to
  `/article/{id}/comment` while logged out, or find the UI path if the
  comment form itself is gated) and confirm the flash renders on
  `article/view.html.twig` with the navbar's "Log In" button visible on the
  same page — no code change here, verification only.

## 9. Run full test suite

- `cd symfony-app && vendor/bin/phpunit tests/` — confirm all tests pass,
  no regressions against the post-items-1–2 baseline (793 tests, per
  `specs/roadmap.md`'s Phase 11 `Done` entry).
