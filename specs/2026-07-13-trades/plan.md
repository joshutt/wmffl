# Phase 8 — Trades: Plan

Each task group is intended to land as its own small PR, in order. Read
paths first, then offer creation, then the risky execution path, then
admin + retirement.

## 1. Foundation: mailer + trade data access

- `composer require symfony/mailer` in `symfony-app/` (sendmail/native
  transport in prod via `MAILER_DSN` in `.env.local`; `null://null` in
  `.env` so dev/test never sends real mail).
- `TradeOfferRepository` (Doctrine, existing `Offer`/`OfferedPlayer`/
  `OfferedPick`/`OfferedPoint` entities): pending offers for a team,
  offer-by-id with full terms (players joined to `newplayers` for
  name/pos/team, picks incl. OrgTeam fallback to giving team, points),
  "whose move is it" from `LastOfferID` (team id — see requirements).
- Expiry semantics in the repository: `Pending` + `Date` older than 7
  days ⇒ treated as `Expired` at read time.
- Doctrine migration + entity for the new `offercomments` table
  (CommentID, OfferID, TeamID, Action, Date, Comment) and the nullable
  `offer.PrevOfferID` chain link; repository method returning an offer's
  comment history across its predecessor chain (chronological, with team
  names).
- `TradeMailer` service: the three plain-text messages (offered /
  accepted / rejected), recipients = both teams' active users, Reply-To =
  acting team's users, From `webmaster@wmffl.com`; offer email links to
  `/trades`. Unit-test message assembly (recipients, sentence rendering
  incl. and/comma rules from legacy `printList`).
- Unit tests against the repository/service layer.

## 2. Trade screen (read-only)

- `TradeController`, `GET /trades`: login-gated; pending (unexpired)
  offers involving your team — other team, offered/expires dates, status
  line ("They made offer…"/"You made offer…"), You-receive / They-receive
  term lists; action buttons rendered per whose-move (Accept/Reject/
  Counter vs Withdraw/Amend) but pointing at routes that arrive in groups
  3–5 (buttons can land disabled or in this group's PR footer note —
  decide at implementation, keep the PR self-contained).
- Comment history per offer: chronological list (team, date, action
  label, text) walked across the amendment chain — empty at first since
  writes arrive in groups 4–5.
- "Offer New Trade": active-team dropdown → offer builder route.
- Twig template in the site layout, `btn-wmffl` styling, transmenu
  include.
- Logged-out state: same message pattern as other transaction pages.

## 3. Offer builder

- `GET/POST /trades/offer` (new / amend / counter share it): two-column
  picker — your tradeable roster and theirs (no HC), your/their owned
  future draft picks straight from `draftpicks` (season, round, original
  owner when traded), transaction points (season `current..+5`, amount,
  remaining balance shown per side).
- Amend/counter arrive with `offerid`; builder pre-selects current terms.
- Server-side validation service (`TradeValidationService`, replaces
  `checkambigous`): every offered player currently on the giving team's
  roster, every pick owned (by draftpicks row id / season+round+orgTeam),
  points within remaining balance, no duplicates, non-empty trade.
  Errors re-render the builder inline (no separate error page).
- CSRF on the POST.

## 4. Confirm + submit offer

- `POST /trades/offer/confirm`: preview page — sentence rendering of both
  sides, comments textarea, Edit / Make Offer / Cancel (terms round-trip
  through hidden fields or session, following the article-publish preview
  pattern).
- Submit: re-validate, then in one transaction insert `offer`
  (`Status='Pending'`, `Date=now()`, `LastOfferID=<your team id>`,
  `PrevOfferID` on amend/counter) + term rows + the comment row
  (action `offered`/`amended`/`countered`, when non-empty); amend/counter
  first marks the predecessor `Modified`. Send the offer email via
  `TradeMailer`. Confirmation page ("Offer Submitted").
- Functional tests: new offer, amend (predecessor → `Modified`,
  `PrevOfferID` set, comment history carries across the chain), counter,
  validation failures; assert email via mailer test transport.

## 5. Respond: accept / reject / withdraw (trade execution)

- `GET /trades/respond/{id}?action=` → confirmation page (question, terms
  sentences, comments box, Yes/No). Counter/Amend skip this and go to the
  builder.
- Reject/Withdraw: set `Reject`/`Withdrawn` respectively, store the
  comment (action `rejected`/`withdrawn`), send the rejected email, back
  to `/trades`.
- Accept: `TradeExecutionService` —
  1. re-validate (by-id roster membership, pick ownership, points
     balance); failure ⇒ auto-reject + explanation page (legacy
     `tradeinvalid` behavior)
  2. inside ONE Doctrine transaction: status → `Accept`; roster swaps
     (close + insert); `draftpicks.teamid` transfers; `transpoints`
     adjustments; `trade` history rows sharing a new TradeGroup; the two
     `transactions` marker rows (`playerid=1`, `method='Trade'`); the
     `accepted` comment row
  3. accepted email after commit
- Guards: only a team in the offer may act; only the non-last-offer team
  may Accept/Reject/Counter; only the last-offer team may Withdraw/Amend;
  expired/settled offers refuse action.
- Heaviest test group: functional tests asserting every DB side effect,
  incl. rollback when a step fails mid-execution.

## 6. Admin oversight

- `GET /admin/trades` (commissioner-gated): all offers newest-first,
  status filter, terms summary, comment history per offer.
- `POST /admin/trades/void/{id}`: void a pending offer (status `Reject`),
  reason stored as a `voided` comment, notification email to both teams
  identifying league action.
- Follows existing admin controller/template conventions.

## 7. Legacy retirement + roadmap

- Delete `football/transactions/trades/` and
  `football/transactions/transmenu.php` (directory now empty).
- 301s in `LegacyTransactionRedirectController`:
  `trades/tradescreen(.php)` and every other `trades/*` file → `/trades`;
  remove the "trades untouched" carve-out comment.
- Update `templates/transactions/_transmenu.html.twig` Trade Offers link
  to `/trades`; sweep for any other legacy-URL references.
- Roadmap: move Phase 8 to Done (note Phase 7 skipped, still pending);
  run through `validation.md`.
