# Requirements — Articles: publishing flow + comments

The rest of roadmap Phase 1 in one branch: step 2 (member
authoring/publishing) and step 3 (article comments). Branch:
`articles-publish-comments`. Builds on the merged read-only phase
(`specs/2026-07-06-articles-read-only/`).

## Scope

1. **Member publish flow** — replaces `football/article/publish.php`,
   `process.php`, `preview.php`, `confirm.php`: any logged-in member writes
   an article (TinyMCE), previews it, then Edits or Publishes.
2. **Article comments** — new functionality: logged-in members comment on
   articles and reply to comments (threaded via the existing `parent_id`
   column); comments render on the article view page.
3. **Admin comment moderation** — per mission.md, the feature ships with
   admin tooling: a moderation page to deactivate/reactivate any comment.
4. **Legacy retirement** — after this branch, the entire `football/article/`
   directory is deleted; every article-related route is Symfony-served.

## Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Publish flow shape | Same UX, saner internals | Keep write → preview → Edit/Publish, but edit-in-place on one article row. Legacy deleted + reinserted the row on Edit (changing its id) and left orphaned inactive rows on abandoned previews. |
| Activation | Self-publish | Parity: the author's Publish button sets `active = 1` immediately. Admin can deactivate afterward via `/admin/articles`. |
| Comments | Threaded replies | Uses the existing `comments.parent_id` schema and the intent of the dead `printComment()` code. Members only; admin deactivate/reactivate. No author edit/delete window. |
| Validation gate | Last phase's gate + coverage bar | Tests + manual E2E + regression, plus ≥95% line coverage on new PHP files (see validation.md). |

## Legacy semantics to preserve (from process.php / confirm.php)

- Only logged-in members can write; the form is `/article/publish` (the
  list page's "Write Article" button already points there).
- Validations: title required, ≥2 chars after trim, <75 chars; exactly one
  of image URL / image upload (error if both or neither); article body of
  at least 200 characters. Validation failures re-show the form with the
  entered values and error messages.
- Image handling: the chosen URL or upload goes through the same pipeline
  as everything else — reuse `ArticleImageService` (resize to 600px,
  blob in `images` table, `img/l/{hash}` link).
- Preview shows the article exactly as the view page renders it, with
  Edit and Publish buttons.
- Publish sets `active = 1` and redirects to the homepage.
- New article rows get `author` = the logged-in user, `priority` = 0.

## Deliberate changes from legacy

- **Edit-in-place:** the draft row (`active = 0`) is created on first
  successful preview and *updated* on subsequent edits. Legacy's
  delete-and-reinsert (new id every edit) is not replicated. Abandoned
  drafts still remain as inactive rows (as in legacy) and are visible in
  `/admin/articles`.
- **displayDate is set at first publish**, not first-preview time, so the
  homepage/list ordering reflects when the article actually went live
  (legacy set it at insert; the difference was minutes at most). It is NOT
  bumped when an already-published article is re-edited and re-published,
  so editing an old article doesn't jump it to the top of the homepage.
- **Last-edited tracking** (new capability): a nullable `lastEdited`
  DATETIME column is added to `articles` (schema migration — the branch's
  first real schema change; safe for remaining legacy code since nothing
  outside the deleted `football/article/` reads that table). It is NULL
  until a *published* article is edited, and set to now() on every save of
  an already-active article — through the member edit flow or the admin
  edit form alike. Draft edits before first publish never set it. The view
  page shows "Edited: {date}" under the Published line when `lastEdited`
  is non-null and falls on a different calendar day than `displayDate`
  (day granularity avoids a same-day "Published/Edited" duplicate line).
- **Authors can edit their own published articles** (new capability —
  legacy members could never edit after publishing). The article view page
  shows an Edit link to the article's author (and commissioners) leading
  into the same publish → preview → confirm flow on the same row. The
  article **stays live (`active = 1`) throughout the edit**; submitted
  changes save to the live row at preview time, so a member abandoning at
  the preview step leaves their last-saved edits visible. Publishing
  confirms and returns home.
- **Preview access:** only the draft's author or a commissioner can view
  `/article/preview/{id}` or confirm it (legacy did no ownership check —
  anyone could activate any article by id).
- The member editor uses classic-mode TinyMCE on a textarea (same as the
  admin form) rather than legacy's inline-mode contenteditable div; same
  toolbar and plugins, simpler form submission. The TinyMCE init is
  extracted to a shared Twig partial used by both forms.

## Comments design

- Schema exists: `comments(comment_id, article_id, comment_text, author_id,
  date_created, active, parent_id)`; `App\Entity\Comment` already mapped.
- Article view page gains: active comments for the article rendered as a
  thread (children indented under parents), each with author name and
  `m/d/y h:i a` timestamp (the dead `printComment()` format); a top-level
  comment form; a reply form per comment (members only — anonymous
  visitors see comments but no forms).
- Posting: non-empty text required; `parent_id`, when present, must be an
  active comment on the same article. Author = logged-in user,
  `date_created` = now, `active` = 1. Redirect back to the article view.
- Comment text is stored as plain text and rendered escaped with line
  breaks (`nl2br` equivalent) — no HTML/WYSIWYG in comments.
- Moderation: `/admin/comments` lists recent comments (including inactive,
  newest first) with article link, author, and an activate/deactivate
  toggle. Deactivating a parent hides its whole subtree from the article
  page (children of hidden parents are not re-parented).

## Out of scope

- Comment counts/badges on article cards, list page, or homepage.
- Email/notification on new comments or replies.
- Member editing of published articles or of their own comments.
- Migrating `forum/` commentary (Phase 7) — the `comments` table used here
  is article comments only.

## Risks / notes

- `comments.active` is nullable in the schema; treat NULL as inactive
  everywhere (only `active = 1` renders).
- After deleting `football/article/`, the legacy URLs `/article/process`
  and `/article/confirm` (POST targets) must not 500 for stale
  tabs/bookmarks — the new flow keeps `/article/publish` as the form URL
  and handles its own POSTs; old POST targets get a redirect to the form.
- `public/base/js/article.js` (inline-mode TinyMCE init) becomes unused
  once the member form switches to the shared classic-mode partial —
  delete it with the legacy files.
