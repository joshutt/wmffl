# Plan — Articles: publishing flow + comments

Task groups in order; each leaves the suite green and the app working.

## 1. Shared prep: TinyMCE partial + lastEdited schema

- Extract the TinyMCE init from `templates/admin/article/form.html.twig`
  into `templates/article/_editor.html.twig` (script include + init taking
  the selector as a variable); admin form switches to the partial.
- Doctrine migration adding nullable `lastEdited` DATETIME to `articles`
  (default NULL); add the mapped property + accessors to
  `App\Entity\Article`.

## 2. Member publish form

- `ArticlePublishController` (`App\Controller`):
  - `GET /article/publish` (name `article_publish`) — members only
    (redirect `/` or show the legacy "must be logged in" message for
    anonymous users); renders `article/publish.html.twig`: title, image
    URL, image upload, caption, TinyMCE body (via the shared partial),
    Preview submit. Prefills from an existing article when editing
    (`?draft={id}`, author/commissioner only — works for drafts AND the
    author's published articles).
  - `POST /article/publish` — port process.php validations: title trimmed
    ≥2 and <75 chars; exactly one of URL/upload; body ≥200 chars. On
    failure re-render the form with entered values and error flashes. On
    success run the image through `ArticleImageService`, create or update
    the row (new articles start `active = 0`, author = user, priority 0;
    an already-published article keeps `active = 1` — it stays live while
    edited) and redirect to the preview.
  - Existing-image note: when editing, an unchanged image means neither
    URL nor upload is submitted — the "exactly one image source" rule
    applies only when the row has no stored link yet; otherwise no image
    input keeps the current one.
- Reuses `ArticleRepository::find()`; add `save()`/persist handling as
  needed.

## 3. Preview + confirm

- `GET /article/preview/{id}` — author or commissioner only (404 unknown
  id, 403 otherwise): renders the article exactly like
  `article/view.html.twig` (reuse the template/partial) plus an
  Edit/Publish button form posting to the confirm route.
- `POST /article/confirm/{id}` — author or commissioner only:
  - `Edit` submit → redirect to `/article/publish?draft={id}` (row kept,
    edit-in-place; no legacy delete/reinsert).
  - `Publish` submit → set `active = 1`, flush, redirect `/`.
    `displayDate = now()` only when the article was not already active
    (first publish) — re-publishing an edited live article keeps its
    original date.
- Article view page: an Edit link (to `/article/publish?draft={id}`)
  visible only to the article's author or a commissioner.

## 3b. Last-edited date

- Every save of an already-active article sets `lastEdited = now()`:
  in the member publish POST (when the row being updated is active) and
  in `AdminArticleController::applyForm()` (when editing an active
  article). Draft saves and first publish leave it NULL.
- `article/view.html.twig`: under the Published line, render
  `Edited: {lastEdited|date('M d, Y')}` when `lastEdited` is non-null and
  its calendar day differs from `displayDate`'s.
- Legacy POST-target compatibility: routes for `/article/process` and
  `/article/confirm` (no id) redirect to `article_publish`.

## 4. Comments — read side

- `CommentRepository` (`App\Repository`, EM-based like `ArticleRepository`):
  - `findActiveByArticle(int $articleId): array` — active comments with
    authors, ordered `date_created ASC`.
  - Tree assembly into roots + children (only children of *visible*
    parents render, so deactivating a parent hides the subtree).
- Article view page (`article/view.html.twig`): comments section under the
  story — recursive rendering via a Twig macro/partial
  (`article/_comment.html.twig`), children indented per depth, author name
  bold + `m/d/y h:i a` date, text escaped with `nl2br`.
- `ArticleController::view()` passes the comment tree (and login state)
  to the template. The preview page renders no comments section.

## 5. Comments — write side

- `POST /article/{id}/comment` (name `article_comment`, members only):
  fields `text` (required non-empty after trim) and optional `parent`
  (must be an active comment belonging to the same article, else error).
  Creates the Comment (author = user, now, active = 1) and redirects back
  to `article_view` (anchor to the new comment).
- Top-level form under the comments; per-comment Reply link revealing a
  reply form (small vanilla-JS toggle, per tech-stack no-framework rule).

## 6. Admin comment moderation

- `AdminCommentController` extending `AbstractAdminController`:
  - `GET /admin/comments` — recent comments newest first (including
    inactive/NULL-active), showing text excerpt, author, article link,
    date, status; `?page=N` if more than 50.
  - `POST /admin/comments/{id}/toggle` — flip active (NULL counts as
    inactive → activating sets true), redirect back.
- Templates under `templates/admin/comment/`; `btn-wmffl` buttons in
  `text-center` wrappers; sidebar link "Comments" in `admin/base.html.twig`.

## 7. Legacy cleanup + roadmap

- Delete the whole `football/article/` directory (`article.php`,
  `articleUtils.php`, `view-snip.php`, `publish.php`, `process.php`,
  `preview.php`, `confirm.php`) and
  `symfony-app/public/base/js/article.js`.
- Grep for stray references to the deleted files/routes across `football/`
  and templates; fix any found.
- Update `specs/roadmap.md`: Phase 1 fully done (move to Done section).

## 8. Tests + validation pass

- Mock-based tests per existing conventions for: publish form
  (auth gate, each validation, draft create vs update, image service
  wiring), preview/confirm (ownership, Edit vs Publish actions,
  displayDate set at publish), comment posting (auth, validation,
  parent-article check), comment tree assembly, admin moderation
  (gate, toggle, NULL-active handling).
- Run the full validation.md checklist including the ≥95% coverage bar
  before opening the PR.
