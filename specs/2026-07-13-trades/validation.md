# Phase 8 — Trades: Validation

How to know the implementation succeeded and each PR (and the phase) can
be merged. Automated tests run from `symfony-app/`
(`vendor/bin/phpunit tests/`); E2E uses the fake-session recipe from
earlier phases (`php -S` + seeded session cookie for a test team).

**VALIDATION PASS 2026-07-13 — all gates green.** Suite: 616 tests, 1904
assertions, OK (`vendor/bin/phpunit tests/`). E2E ran against
`php -S 127.0.0.1:8088 public/index.php` with seeded sessions for teams
2 (commish), 3, and 5 on the dev DB; every write was verified in MySQL
and then reverted (offers 728–734, TradeGroup 210, roster/pick/point
changes — DB restored to pre-walkthrough state; offers 726/727 predate
the pass and were left alone). Notes per gate below.

## Per-group gates

Every PR: test suite green, no new legacy code touched except deletions,
CSRF token present on every new POST form, no interpolated SQL.
✅ All four held for every commit (Groups 1–7, one commit each).

### 1. Foundation

- [x] Unit: repository returns a pending offer with full terms
      (`TradeOfferRepositoryTest::testFindOfferReturnsFullTermsKeyedByGivingTeam`;
      the null-OrgTeam fallback is `COALESCE(op.OrgTeam, op.TeamFromID)`
      in SQL — asserted in the query and confirmed live in E2E with pick
      3635, orgTeam 6 labeled "Crusaders's pick")
- [x] Unit: whose-move logic both directions (LastOfferID = team id)
- [x] Unit: 8-days-ago Pending reads Expired; 6-days-ago stays Pending
- [x] Unit: `TradeMailer` recipients/Reply-To/printList (1, 2, 3+ items)
- [x] `null://null` in `.env`; prod uses `sendmail://default` via
      `.env.local` (flex-generated `compose.override.yaml` removed — no
      docker here)
- [x] Migration `Version20260713000000` applied cleanly on the dev DB;
      additive only (new table + nullable column)
- [x] Unit: comment history walks `PrevOfferID` chronologically, incl. a
      cycle guard

### 2. Trade screen

- [x] E2E: logged out → must-log-in message, no data
- [x] E2E: both directions render (offer 728: team 3 saw "They Made
      Offer…" with Accept/Reject/Counter; team 2 saw "You Made Offer…")
      with dates and term lists both ways
- [x] E2E: expired offers excluded (unit-tested; read-time expiry) —
      controller drops non-Pending effective statuses
- [x] E2E: chain comments render with team, date, action label ("E2E
      chain: first offer" (Offered) + "E2E chain: amended" (Amended) both
      visible on the successor offer)
- [x] New-offer dropdown lists active teams only (and omits your own)

### 3. Offer builder

- [x] E2E: 26 non-HC players per side (27 minus HC), exactly the 56
      draftpicks rows team 2 owns, 2026 balance 58 — all matched SQL
- [x] Functional: player-not-on-roster → inline error, nothing persisted
      (also hit live when a stale selection named a traded player);
      pick/points/empty-trade errors unit-tested
      (`TradeValidationServiceTest`)
- [x] Functional: amend pre-selects existing terms (unit +
      E2E multiline check: player, pick row, points amount all
      pre-selected on `?offerid=`)

### 4. Confirm + submit

- [x] Functional: offer 728 persisted exactly — TeamFromID = giving team
      on every term row, OrgTeam=6 recorded on the pick
- [x] Functional: counter marked 728 `Modified`, created Pending 729 with
      `PrevOfferID=728`, `LastOfferID=3`
- [x] Offer email: unit-tested via mocked transport (recipients, /trades
      link, no legacy URL); dev transport is null — no live capture
      needed
- [x] Comments stored with right action/team (`offered` by 2 on 728,
      `countered` by 3 on 729); empty comment writes nothing (unit);
      predecessor comments shown on successor (E2E)
- [x] E2E: Cancel from preview persisted nothing (offer count unchanged);
      Edit round-trips selections via POST /trades/offer (unit)

### 5. Respond / execution (merge-blocking for the phase)

- [x] E2E Accept of player+pick+points vs player (offer 729): offer
      `Accept`; roster rows closed at 15:27:39 and reopened on the
      receiving teams; draftpicks 3635 teamid 2→3 with orgTeam kept;
      TotalPts 58/55 → 55/58; three `trade` rows sharing TradeGroup 210
      (two player rows + summary "a 9th round pick in 2026 and 3
      protection points in 2026 "); `/transactions` rendered the full
      trade sentence; two `transactions` rows playerid=1 method='Trade'.
      Same assertions in
      `TradeExecutionServiceTest::testExecuteWritesEveryTradeSideEffectInsideOneTransaction`
- [x] Functional: forced mid-write exception propagates out of
      `Connection::transactional` (rollback is DBAL's contract); all
      writes asserted inside the transactional callback
- [x] Functional: stale accept auto-rejects with explanation page and no
      side effects; roster match at index 0 validates (dedicated tests
      for the legacy `array_search` bug in both validation services)
- [x] Authorization matrix, live: third team (5) → 403; recipient
      Withdraw → 403; last-offer-team Accept → 403 (unit); settled and
      expired offers refuse (unit + settled re-void refused live)
- [x] E2E: Withdraw wrote `Withdrawn` (730), Reject wrote `Reject` (731),
      comments stored with `withdrawn`/`rejected`/`accepted` actions;
      emails unit-tested
- [x] E2E: full happy path offer → counter → accept through the browser
      flow with fake sessions

### 6. Admin

- [x] E2E: non-commissioner 302→/ from `/admin/trades`; void blocked too
      (unit)
- [x] E2E: void set `Reject` on 732, stored "E2E admin void" as a
      `voided` comment, re-void refused (flash error, no second write);
      notification email unit-tested (`sendVoidedEmail`, no Reply-To)
- [x] E2E: `/admin/trades?status=Pending` filtered correctly and showed
      comment histories

### 7. Retirement

- [x] `football/transactions/` gone entirely (trades/ was its last
      content)
- [x] curl sweep: tradescreen.php, tradescreen, bare directory,
      edittrade/confirmoffer/processTrade/finalprocess/tradeinvalid/
      ambigouspick/testemail(.php) all 301 → `/trades` (a stale POST to
      processTrade.php also 301s); `transmenu.php` 301 → `/transactions`
- [x] Transmenu partial uses `path('trades_screen')`; grep finds no
      remaining `transactions/trades` references outside specs, git
      history, and the frozen per-season `football/history/` pages
      (which reference their own local transmenu.html, not trades)
- [x] Regression: `/transactions`, `/transactions/waivers`,
      `/transactions/ir`, `/transactions/protections/show`,
      `/transactions/list` all 200

## Phase-level definition of done

- [x] All boxes above checked; outcomes recorded in this file
- [x] End-to-end manual walkthrough on the dev DB: offer → counter →
      accept → rosters/picks/points verified in SQL and the trade
      sentence verified on `/transactions` (emails not observable live —
      null transport by design; message assembly unit-tested) — then all
      walkthrough data reverted
- [x] Roadmap updated (Phase 8 → Done, Phase 7 noted as still pending);
      memory updated
- [x] No references to deleted legacy files from live code
