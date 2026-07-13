# Phase 8 — Trades: Requirements

## Scope

Migrate the entire trade workflow from `football/transactions/trades/`
(~2,000 lines) to Symfony: the trade screen (pending offers + new-offer
entry), the offer builder (offer → confirm → submit), the response flow
(accept / reject / withdraw / counter / amend), trade execution, and email
notifications. Land as small sequential PRs per task group in `plan.md`,
riskiest writes (trade execution) last.

Decisions made 2026-07-13 (with Josh, via AskUserQuestion):

1. **Scope: full workflow in one spec** — both roadmap steps (read views
   and the write path), not read-only-first.
2. **Email: symfony/mailer with the sendmail/native transport** — same
   delivery path the legacy PHP `mail()` uses today; `null://` transport in
   dev/test so no real mail is sent locally.
3. **Extras in scope (all three):**
   - **Admin trade oversight** — new page for commissioners to view all
     offers and void any pending one (legacy has no admin tooling for
     trades; the mission doc requires admin coverage per area).
   - **In-app expiry** — treat `Pending` offers older than 7 days as
     expired at read time rather than relying solely on the nightly SQL job
     (`scripts/database/nightlyqueries.sql` stays as belt-and-braces).
   - **Modernized offer builder** — replace the legacy checkbox wall with a
     cleaner picker UI (see below); not a faithful layout port.
4. **Stored trade comments** (added by Josh, 2026-07-13) — legacy sends
   the free-text comments in the notification emails and then discards
   them. New behavior: every comment entered anywhere in the flow (offer,
   amend, counter, accept, reject, withdraw, admin void) is persisted and
   shown as a running history on the trade screen. See "Trade comments"
   below.

Standard hardening applies throughout (as in Phases 1–5): Doctrine/bound
parameters (legacy code interpolates everything — SQL injection
throughout), CSRF tokens on all POSTs, and a DB transaction wrapped around
trade execution (legacy runs ~8 separate unguarded statements).

## Legacy flow (what we're replacing)

| Legacy file | Role |
|---|---|
| `tradescreen.php` | Pending offers for your team + "offer new trade" team picker |
| `edittrade.php` | Offer builder: checkboxes for players (both rosters), draft-pick year/round dropdowns, transaction-points inputs; Update / Confirm / Cancel |
| `checkambigous.inc.php` | Validates picks exist (`draftpicks`) and points balances (`transpoints`) |
| `ambigouspick.php` | Error page for invalid/ambiguous picks — **half-implemented**: most of its content is hardcoded 2003/2004 sample text |
| `confirmoffer.php` | Preview terms + comments box (terms carried in `$_SESSION`) |
| `processconfirm.php` | Saves the offer (`saveOffer`), marks a predecessor `Modified`, emails both teams |
| `processTrade.php` | Dispatch on Accept/Reject/Counter/Withdraw/Amend; Amend/Counter re-enter `edittrade.php`, others get a yes/no confirmation page |
| `finalprocess.php` | Reject/Withdraw: set status + email. Accept: `validateTrade` → email → `acceptTrade` |
| `loadTrades.inc.php` | All data access: load/save offers, `validateTrade`, `acceptTrade` (the execution) |
| `trade.class.php` | Dumb value objects (Trade/Team/Player/Pick/Points) |
| `tradeinvalid.php` | Accept-time validation failure page (also auto-rejects the offer) |
| `testemail.php` | Dead test script — delete, no replacement |

### Trade execution (`acceptTrade`) — what "accept" does

1. `offer.Status` → `'Accept'`
2. Players: close current `roster` rows (`dateoff=now()`), insert new rows
   on the receiving team
3. Picks: `UPDATE draftpicks SET teamid=<new>` matched on
   season/round/teamid/orgTeam
4. Points: `transpoints.TotalPts` += on receiver, -= on giver, per season
5. History: insert one `trade` row per player + one per side's
   picks/points summary sentence, all sharing a new `TradeGroup`
   (max+1) — this feeds the existing `TransactionHistoryService` trade
   sentences on `/transactions`
6. Insert two `transactions` rows (`method='Trade'`, hardcoded
   `playerid=1`) — quirk kept for compatibility with transaction counts

### Emails (3 kinds)

All: From `webmaster@wmffl.com`, To = both teams' users
(`user.active='Y'`), Reply-To = acting team's users, plain text, terms as
a sentence (`printList`), plus free-text comments.

- **Offer made** (`processconfirm.php`) — includes a link to the trade
  screen (must point at the new route) and the 7-day expiry note
- **Accepted** (`finalprocess.php`)
- **Rejected/withdrawn/cancelled** (`finalprocess.php`, subject "Trade
  Offer Rejected")

## Trade comments (new capability)

Legacy collects comments in three places (`confirmoffer`/`processconfirm`
on submit, `finalprocess` confirmation on accept/reject/withdraw), pastes
them into the email, and stores nothing. New behavior:

- **Store every comment** in a new `offercomments` table:
  `CommentID` (PK auto), `OfferID`, `TeamID` (who wrote it), `Action`
  (offered / amended / countered / accepted / rejected / withdrawn /
  voided), `Date`, `Comment` (text). A row is written whenever a
  non-empty comment is submitted; multiple per offer over its life.
- **Chain linking**: an amend/counter creates a *new* offer row, so
  comments attached to the predecessor would disappear from view. Add a
  nullable `offer.PrevOfferID` column, populated by the new code on
  amend/counter (legacy rows stay NULL). The trade screen walks the chain
  to show the full comment history of the negotiation. (This revises the
  "keep the unlinked model" position below — comments are the reason the
  chain now needs linking.)
- **Display**: each offer on `/trades` shows its comment history
  (chronological: team, date, action label, text) gathered across its
  predecessor chain. `/admin/trades` shows the same history per offer.
- **Emails unchanged**: comments still go out in the notification, as
  today.
- History starts at cutover — comments made through the legacy screens
  before the new flow lands were never stored and cannot be backfilled.

## Database

Tables (all already have Symfony entities except the new `offercomments`:
`Offer`, `OfferedPlayer`, `OfferedPick`, `OfferedPoint`, `Trade`,
`TransPoint`, `DraftPick`, `Roster`, `User`):

- `offer` — OfferID, TeamAID, TeamBID, Status
  enum(`Accept,Reject,Pending,Withdrawn,Expired,Modified`), Date,
  LastOfferID
- `offeredplayers` / `offeredpicks` / `offeredpoints` — terms, keyed
  OfferID + TeamFromID (the team giving the item up)
- `draftpicks`, `transpoints`, `roster`, `trade`, `transactions` — touched
  by execution

Schema changes (Doctrine migration, task group 1): the new
`offercomments` table and the nullable `offer.PrevOfferID` column — both
additive, no legacy code reads them. **Phase 7 (table renames) has been
skipped for now** — this phase works against the current names
(`newplayers` etc., via existing entities).

### Legacy quirks to be aware of (and what we do about them)

- `offer.LastOfferID` **stores a team id**, not an offer id — it means
  "team that made the most recent offer" and drives whose turn it is.
  Keep the semantics, name the entity accessor honestly
  (e.g. `lastOfferTeamId`).
- Withdraw and Reject both write `Status='Reject'`; the `Withdrawn` enum
  value is never used. New code should write `Withdrawn` for withdrawals
  (the status enum already supports it; nothing reads the distinction
  today — verify before landing).
- Amending marks the old offer `Modified` and inserts a *new* offer row;
  the chain is not linked (LastOfferID doesn't point at the predecessor).
  Keep the new-row-per-amendment model, but link the chain going forward
  via the new nullable `offer.PrevOfferID` so the stored comment history
  (see "Trade comments") follows the negotiation.
- `validateTrade` uses `!array_search(...)` — a match at index 0 reads as
  a failure, and object identity comparison is fragile. The Symfony
  validation must compare by player id, not reproduce this bug.
- `offeredpicks.OrgTeam` is nullable; when null the pick is assumed to be
  the giving team's own. The legacy "ambiguous pick" flow (team holds two
  picks in the same round) was never finished. The modernized builder
  sidesteps this: users pick from the team's **actual owned picks**
  (`draftpicks` rows), so OrgTeam is always known and recorded.
- Head coaches (`pos='HC'`) are excluded from tradeable rosters.
- Draft-pick season dropdown ran `currentSeason+1..+5` (minus one when
  `currentWeek == 0`); points seasons `currentSeason..+5`. The builder
  should instead offer whatever future picks the team actually owns in
  `draftpicks`, and points for `currentSeason..+5`.

## New surface

Routes (all require login via `AuthenticationService`; follows the
`/transactions/*` grouping used in Phase 4):

- `GET /trades` — trade screen: pending offers involving your team
  (status label, terms both ways, offered/expires dates), the offer's
  comment history across its amendment chain, action buttons per offer
  (Accept/Reject/Counter when it's your move; Withdraw/Amend when you
  made the last offer), and the new-offer team picker.
  Offers past the 7-day expiry render as expired / are excluded.
- `GET/POST /trades/offer` — offer builder (new offer, amend, counter):
  pickers for both teams' rosters (no HC), the giving team's owned draft
  picks, and transaction points (season + amount with remaining balance
  shown). Server-side validation replaces `checkambigous`: pick ownership,
  points balance, dedupe.
- `POST /trades/offer/confirm` — preview terms + comments → submit.
  Saving marks the predecessor `Modified` (amend/counter, setting
  `PrevOfferID` on the successor), inserts the offer + term rows, stores
  the comment, sends the offer email. Terms travel in the POST (or
  session, matching the article-publish preview pattern).
- `GET/POST /trades/respond/{id}` — confirmation page for
  Accept/Reject/Withdraw with comments box (stored + emailed), then
  execute. Accept
  re-validates (roster membership by id, pick ownership, points balance)
  and on failure auto-rejects with the explanation page (legacy
  `tradeinvalid.php` behavior). Execution runs in one DB transaction.
- `GET /admin/trades` + `POST /admin/trades/void/{id}` — commissioner
  oversight (`AuthenticationService::isCommissioner`): all offers with
  their comment histories, filter by status, void any pending offer
  (sets `Reject`, stores the reason as a `voided` comment, notification
  email identifying league action).

Retirement: delete `football/transactions/trades/` **and**
`football/transactions/transmenu.php` (trades was its last consumer —
this empties `football/transactions/` entirely), 301s from
`/transactions/trades/tradescreen(.php)` and friends in
`LegacyTransactionRedirectController`, update
`templates/transactions/_transmenu.html.twig` to the new route.

## Out of scope

- Trade *history* display — already served by `/transactions`
  (`TransactionHistoryService` sentences); execution keeps feeding it.
- Offer-chain data model changes, notification preferences, HTML email.
- Phase 7 table renames (skipped; separate phase).
- Retiring the nightly expiry SQL (stays as a safety net).
