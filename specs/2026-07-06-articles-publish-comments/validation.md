# Validation — Articles: publishing flow + comments

Mergeable when all of the below pass.

## Automated tests + coverage bar

- `cd symfony-app && vendor/bin/phpunit tests/` — all green.
- Coverage: `vendor/bin/phpunit tests/ --coverage-clover coverage.xml`,
  then **≥95% combined line coverage across the new PHP files** of this
  branch (publish controller, confirm handling, CommentRepository,
  comment posting, AdminCommentController). Measure from coverage.xml the
  same way as last phase.

Required test coverage areas:

- Publish form: anonymous users can't reach form or POST; each process.php
  validation (short/missing/long title, both/neither image source, <200
  char body) re-renders with the right error and persists nothing; valid
  POST creates a draft (`active = 0`, author set, priority 0) and
  redirects to preview; editing an existing draft updates the same row
  (id unchanged); image URL and upload both route through
  ArticleImageService; both-image-sources conflict errors.
- Preview/confirm: unknown id 404s; non-author non-commissioner gets 403;
  author and commissioner both allowed; Edit redirects to the prefilled
  form without deleting the row; Publish sets `active = 1`, sets
  displayDate on first publish only (re-publishing an already-active
  article keeps the original date), flushes, redirects home.
- Editing published articles: the author can load their published article
  into the form and it stays `active = 1` through save/preview; a
  different (non-commissioner) member cannot; edits without a new image
  source keep the stored link; the view page shows the Edit link only to
  the author or a commissioner.
- Last-edited date: NULL through draft saves and first publish; set on a
  member edit of a published article and on an admin edit of an active
  article; NOT set by an admin edit of an inactive draft; view template
  shows the Edited line only when lastEdited is non-null and on a
  different calendar day than displayDate (same-day edit shows nothing).
- Comments: anonymous POST rejected; empty text rejected; parent from a
  different article rejected; inactive parent rejected; valid top-level
  and reply comments persist with author/date/active; tree assembly nests
  children and drops subtrees of inactive parents; NULL active treated as
  inactive.
- Admin moderation: non-commissioner redirected; list includes inactive
  and NULL-active comments; toggle flips (and NULL → active).

## Manual end-to-end (real DB, via dev server)

- [ ] Logged-in member: write an article with an uploaded image →
      preview shows it exactly as the view page will → Edit returns to the
      form with all fields (including image) intact and the same draft id →
      change something → preview again → Publish → article appears on the
      homepage and `/articles`, image serves from `/img/l/{hash}`.
- [ ] Same flow with a remote image URL instead of an upload.
- [ ] Author edits their own published article from the view page's Edit
      link: the article never disappears from the site during the edit,
      the image survives without re-entering it, and the homepage position
      (displayDate) is unchanged after re-publishing.
- [ ] After that edit (on a later day than publication or with the date
      manipulated in the DB), the view page shows "Edited: {date}" under
      the Published line; a never-edited article shows no Edited line.
- [ ] Validation errors render legibly on the form (try a 100-char body).
- [ ] Anonymous visitor: `/article/publish` shows the logged-out message;
      article view shows comments but no comment/reply forms.
- [ ] Member: post a top-level comment and a nested reply; both render
      threaded with correct author/timestamp; posting returns to the
      article anchored at the new comment.
- [ ] Admin: deactivate a parent comment → it and its replies disappear
      from the article page; reactivate → thread returns.
- [ ] `/admin/comments` pagination (if >50 comments) and toggles work.

## Regression (post-cleanup)

- [ ] `football/article/` no longer exists; `/article/publish`,
      `/article/process`, `/article/confirm` all resolve in Symfony (no
      LegacyBridge "Unhandled legacy mapping" errors).
- [ ] Read-only phase intact: homepage, `/articles`, `/article/{id}`,
      legacy 301 redirects, admin articles CRUD + image upload.
- [ ] Untouched legacy routes still load (e.g. `/transactions/transactions`,
      `/history/`, `/rules/`).
- [ ] Root legacy suite (`vendor/bin/phpunit test/`) shows only the
      pre-existing 5 `scoreK()` errors.

## Merge gate

All tests green, ≥95% new-code line coverage, every checkbox above ticked,
and `specs/roadmap.md` marks Phase 1 complete.
