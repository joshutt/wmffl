# Phase 8 — Trades: Validation

How to know the implementation succeeded and each PR (and the phase) can
be merged. Automated tests run from `symfony-app/`
(`vendor/bin/phpunit tests/`); E2E uses the fake-session recipe from
earlier phases (`php -S` + seeded session cookie for a test team).

## Per-group gates

Every PR: test suite green, no new legacy code touched except deletions,
CSRF token present on every new POST form, no interpolated SQL.

### 1. Foundation

- [ ] Unit: repository returns a pending offer with full terms (players
      with name/pos/NFL team, picks with correct original owner incl. the
      null-OrgTeam fallback, points) — seed via fixtures/SQL
- [ ] Unit: whose-move logic correct for both directions (LastOfferID
      = team id)
- [ ] Unit: a `Pending` offer dated 8+ days ago reads as expired; 6 days
      ago does not
- [ ] Unit: `TradeMailer` — recipients are both teams' `active='Y'`
      users, Reply-To only acting team, sentence list uses commas +
      final "and" (matches legacy `printList` output for 1, 2, and 3+
      items)
- [ ] `null://null` transport in dev: no real mail possible locally
- [ ] Migration applies cleanly (`offercomments` table +
      `offer.PrevOfferID`) and is additive — legacy pages unaffected
- [ ] Unit: comment history walks the `PrevOfferID` chain in
      chronological order across an amended offer

### 2. Trade screen

- [ ] E2E: logged out → `/trades` shows the must-log-in message, no data
- [ ] E2E: logged in with seeded pending offers → both directions render
      with correct status line, dates, and term lists both ways
- [ ] E2E: expired seeded offer does not render as actionable
- [ ] E2E: seeded comments (incl. on a `Modified` predecessor) render on
      the offer with team, date, and action label
- [ ] New-offer dropdown lists active teams only

### 3. Offer builder

- [ ] E2E: builder shows both rosters without HC entries, only picks the
      team actually owns in `draftpicks`, and correct remaining points
      balances
- [ ] Functional: submitting a player not on the giving roster / a pick
      not owned / points beyond balance / an empty trade → inline errors,
      nothing persisted
- [ ] Functional: amend pre-selects the existing terms

### 4. Confirm + submit

- [ ] Functional: full flow persists `offer` + term rows exactly
      (TeamFromID = giving team on every row; OrgTeam recorded on picks)
- [ ] Functional: amend/counter marks predecessor `Modified` and creates
      a new `Pending` offer
- [ ] Functional: offer email captured by test transport — both teams'
      users addressed, link points to `/trades` (not the legacy URL)
- [ ] Functional: non-empty comment on submit stored with the right
      action (`offered`/`amended`/`countered`) and team; empty comment
      stores nothing; amend sets `PrevOfferID` and the trade screen shows
      the predecessor's comments on the successor
- [ ] E2E: Cancel and Edit from the preview don't persist anything

### 5. Respond / execution (merge-blocking for the phase)

- [ ] Functional: Accept, seeded 2-player + pick + points trade — assert
      ALL of: offer `Accept`; old `roster` rows closed with `dateoff`,
      new rows on receiving teams; `draftpicks.teamid` moved (matched
      incl. orgTeam); `transpoints.TotalPts` +/- correct per side and
      season; `trade` rows share one new TradeGroup and produce a correct
      sentence on `/transactions` (TransactionHistoryService); two
      `transactions` rows `method='Trade'`
- [ ] Functional: mid-execution failure (e.g. forced exception) rolls
      back EVERYTHING — no partial trade
- [ ] Functional: accept of a stale offer (player since traded/dropped,
      pick since moved, points since spent) → auto-reject + explanation,
      no side effects; a roster match at array index 0 validates
      correctly (legacy `array_search` bug must not reproduce)
- [ ] Functional: authorization matrix — third team gets 403/refusal;
      last-offer team cannot Accept; non-last-offer team cannot Withdraw;
      settled/expired offers refuse all actions
- [ ] Functional: Withdraw writes `Withdrawn`, Reject writes `Reject`,
      both email; respond-time comments stored with the matching action
      (`accepted`/`rejected`/`withdrawn`)
- [ ] E2E: full happy path through the browser flow with fake session

### 6. Admin

- [ ] Functional: non-commissioner blocked from `/admin/trades` and void
- [ ] Functional: void sets `Reject`, stores the reason as a `voided`
      comment, emails both teams, refuses on already-settled offers
- [ ] Functional: `/admin/trades` shows comment histories

### 7. Retirement

- [ ] `football/transactions/` directory is gone entirely
- [ ] curl: `/transactions/trades/tradescreen.php` and `.php`-less
      variant → 301 → `/trades`; every legacy trades file 301s somewhere
      sensible
- [ ] Transmenu partial links to `/trades`; grep finds no remaining
      references to `transactions/trades` outside specs/history
- [ ] Legacy regression sweep: `/transactions`, `/transactions/waivers`,
      `/transactions/ir`, protections pages still work

## Phase-level definition of done

- All boxes above checked; outcomes recorded in this file (as in prior
  phases' validation passes)
- An end-to-end manual walkthrough on a dev DB copy: offer → email →
  counter → accept → verify rosters/picks/points/history on the real
  pages (`/team/{id}/roster`, `/transactions`)
- Roadmap updated; memory updated with any new gotchas
- No references to deleted legacy files from live code (LegacyBridge
  404s on them is fine — that's the redirect controller's job now)
