# Validation — Phase 16b: Draft Results under Transactions

Implementation is ready to merge when all of the following hold.

## Automated tests

- [ ] `symfony-app/vendor/bin/phpunit tests/` passes in full — the whole
      existing suite stays green (892 tests as of `cc1312a`). Use
      `--coverage-clover coverage.xml` if coverage is requested, never
      `--coverage-text`.
- [ ] Repository tests cover: a complete season, the partial 2006 season
      (undrafted rows present, not dropped), the in-progress 2026 season,
      a NULL-pick skeleton season (2027, exercised at the repository level
      since the route 404s) with skeleton rows ordered last within their
      round and franchise names resolving through the `team` fallback, each of the five filters individually and at
      least one combination, and as-of date resolution including the
      no-week-1-row fallback.
- [ ] Service tests cover default-year resolution both ways (current
      season has selections → itself; has none → most recent that does),
      filter normalization (`ALL` / empty / absent all mean no filter),
      and prev/next year being null at each end of the range.
- [ ] Controller tests cover: `/transactions/draftresults` with no year,
      explicit `/2006` and a modern year, an unknown year 404ing, and a
      filtered query string rendering a filtered board with the selections
      echoed back into the form.
- [ ] Admin controller tests cover: non-commissioner redirected, bad/missing
      CSRF 403, set-player persists, clear-player persists, and assigning a
      player already drafted that season is rejected with a flash rather
      than a 500.
- [ ] Redirect tests cover both `draftresults` and `draftresults.php` for a
      sample of years across 2007–2025, asserting a 301 to the correct
      `/transactions/draftresults/{year}`, and that a POST redirects too.

## Parity spot-checks (before the legacy files are deleted)

- [ ] For at least 3 seasons spanning the eras — one 12×10 (2007–2009),
      one 12×12 (2010–2012), one 16×12 (2013+) — compare the new board
      row-for-row against the legacy page: same round/pick ordering, same
      franchise names, same player names, same positions, and confirm the
      round and pick counts match the season's actual shape.
- [ ] For those same seasons, compare the **NFL column** against the
      legacy rendering and record how many cells differ. Differences are
      expected (legacy used a hardcoded draft-day `$dateSet`, the new page
      uses week-1 `ActivationDue`) but each difference should be
      explainable as a real roster move in that window — a wholesale
      mismatch means the as-of join is wrong.
- [ ] Confirm every one of the five filters actually filters, including
      OL / DL / LB / DB, which never worked on the legacy page (its
      `<option name="OL">` typo). Confirm a filtered URL is shareable —
      pasting it fresh reproduces the same filtered board.
- [ ] Confirm the Round and Pick dropdowns show the right ranges per
      season (up to 16 rounds for 2013+, 10 vs 12 picks pre/post 2010),
      not the legacy hardcoded 1–12.

## Traded-pick provenance

- [ ] The "from <franchise>" note appears on exactly the traded rows and
      no others. Row counts to check against: 26 traded picks in 2007
      (the busiest season), 2 in 2019 (the quietest), 1 in 2026.
- [ ] Both franchise names on a traded row are the **season-specific**
      names, not today's. 2007 round 1 pick 1 is the reference case:
      `Gallic Warriors`, `from Pretend I'm Not Here` — team 1 is named
      `Rocky Mountain Oysters` today, so seeing that name instead means
      the join fell through to the `team` fallback when it shouldn't have.
- [ ] A round where one franchise holds several picks acquired from
      different teams renders each with its own correct origin (2007
      round 1: Lindbergh Baby Casserole holds picks 3, 5 and 10, from
      three different franchises).
- [ ] The Team filter matches the **owning** franchise only: filtering
      2007 by Lindbergh Baby Casserole returns its acquired picks and
      does not return the picks it traded away.
- [ ] The note is visually secondary to the owning franchise name — the
      board still scans cleanly on a season where most rows have no note.

## Partial and future drafts

- [ ] `/transactions/draftresults/2006` shows all 120 rows with the 37
      real selections and 83 em-dashed ones — not a 37-row board.
- [ ] `/transactions/draftresults/2026` (the in-progress draft) shows the
      full 192-row board with the selections made so far and blanks after,
      and re-check it after Josh enters another pick to confirm it
      reflects new picks without a cache clear.
- [ ] `/transactions/draftresults/2027`, `/2028` and `/2029` all **404**
      — no future draft is reachable, even though `draftpicks` holds full
      skeletons for them.
- [ ] The 404 for a future season is indistinguishable from the 404 for a
      year that never existed (2050): same status, same page, no "the
      draft hasn't happened yet" hint leaking the difference.
- [ ] The rollover guards are still verified, just not through the route:
      a repository-level test renders 2027 directly and confirms franchise
      names resolve through the `team` fallback (`teamnames` has no 2027
      rows) and that NULL-pick rows order last within their round, rather
      than the board collapsing to zero rows.
- [ ] `/transactions/draftresults` (no year) resolves to 2026 today, and a
      test proves it would fall back to the most recent season with
      selections if the current season had none.
- [ ] `/transactions/draftresults/2005` (skeleton-only, all-NULL playerid)
      and `/transactions/draftresults/2004` (no rows at all) — confirm the
      chosen 404-vs-render behavior matches `requirements.md`: 2004 404s;
      2005 renders as a skeleton board (it has rows). The cut-off must not
      have swept up empty *past* seasons along with future ones.

## Navigation and links

- [ ] Prev/next year links step correctly across the whole range, and the
      link is absent (not a dead link) at the first and last season.
- [ ] On the current season's board (2026 today) there is **no** "next"
      link — the reachable range ends at the current season, so nothing
      offers a route into 2027.
- [ ] The "Draft Results" button appears on the transactions button bar on
      every transactions page that includes `_transmenu.html.twig`, and
      links to the year-less route so it never goes stale.
- [ ] Every one of the 19 updated legacy link sites lands on the correct
      new URL — spot-check at least one flat hub page (2007–2017) and one
      season-directory index (2018–2025) of each markup style.
- [ ] `grep -rn "draftresults" football/ symfony-app/` comes back clean of
      references to the deleted files (only the redirect controller, the
      new route names, and roadmap/spec prose remain).

## Admin

- [ ] A commissioner can set a missing pick's player and clear a wrong
      one, and both changes show immediately on the public board.
- [ ] A non-commissioner (logged-in member and logged-out visitor) cannot
      reach `/admin/draftresults/{year}` or POST to its endpoints.
- [ ] The admin page is reachable from the admin dashboard — no
      URL-typing required.
- [ ] `/admin/draftresults/2027` 404s for a commissioner, and a
      hand-crafted POST against a 2027 pick id is rejected rather than
      persisting — the cut-off is enforced on the write path, not just
      the page.
- [ ] Attempting to assign a player already drafted in that season shows a
      readable error, leaves the data unchanged, and does not 500 on the
      `Season_playerid_uniq` constraint.

## Cleanup confirmation

- [ ] `football/history/common/draftresults.php` and all 19 per-year
      wrappers are deleted (via `git rm`).
- [ ] Both legacy URL forms for every year 2007–2025 301 to the new route
      rather than 404ing or falling through to the LegacyBridge.
- [ ] No new CSS file was added (the legacy
      `football/base/css/draftresults.css` is empty and its only reference
      is being deleted; leave the file itself to the eventual `base/`
      teardown).

## Sign-off

- [ ] Josh has reviewed the rendered board for a complete historical
      season, the partial 2006 season, and the in-progress 2026 draft, and
      confirms it is an acceptable replacement for the legacy pages before
      the branch is merged.
- [ ] Josh has confirmed the week-1-as-of NFL column is acceptable in
      place of the legacy hardcoded draft-day date, having seen the
      difference count from the parity spot-check above.
- [ ] Josh has confirmed the future-season cut-off behaves as intended at
      the boundary — the current season reachable, the next one not — and
      is aware it advances automatically at the next rollover rather than
      needing a yearly edit.
