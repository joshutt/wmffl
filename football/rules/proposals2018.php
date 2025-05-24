<?
session_start();
$title = "WMFFL Rule Proposals";

$cssList = array("/base/css/rules.css");
include "base/menu.php"; 
?>

<h1 align=center>Current Rule Proposals</h1>
<hr SIZE = "1"/>

<p>This is the list of proposals to be voted on for the 2018 WMFFL season.
If you would like to suggest a rule proposal, you may
do so on the <a href="proposesubmit.php">proposals page</a>.  The part of
each proposal that appears in <i>Italics</i> is what effect this proposal will
have on the ruleset, if passed.</p>

<p>
<b>Proposal 2018.1 - Add Home Field Advantage</b><br/>
<b>Sponsor: Steve Schillinger</b><br/>
<span class="ballot">Status: <span class="status">Withdrawn</span></span><br/>
Add a home field advantage that will be small, maybe 2 points.  There would be a number of factors that can adjust that
up or down.  If the team is on a winning streak the points would be higher and would be lower for a losing streak.  A team
in first place would get a slight bump as well.  The exact details will be worked out later.
<blockquote><i>Details TBD</i></blockquote>
</p>

<p>
<b>Proposal 2018.2 - Adjust Game Plan For to 50%</b><br/>
<b>Sponsor: Josh Utterback</b><br/>
<span class="ballot">Status: <span class="status">Withdrawn due to 2018.4</span></span><br/>
Game planning for scores too many points.  Adjust it to match the Game Plan Against score, which is 50% of the base score.
<blockquote><i>Update rule V.D.1 to read:
        <blockquote>1. The player on their team that is game planned for will have their score increased by 50%, with fractions rounded down.<br/>
        </blockquote>
    </i></blockquote>
</p>

<p>
    <b>Proposal 2018.3 - Negative Game Plan For Increase Not Decrease</b><br/>
    <b>Sponsor: Brian Elliff</b><br/>
    <span class="ballot">Status: <span class="status">Withdrawn due to 2018.4</span></span><br/>
A game planned for player that scores negative has their score doubled (from -8 to -16).  Reverse that and half it.
<blockquote><i>Add rule V.D.1.a to read:
        <blockquote>a. If a game planned for player scores negative their score will be halved, not doubled.<br/>
        </blockquote>
    </i></blockquote>
</p>


<p>
    <b>Proposal 2018.4 - Remove Game Planning</b><br/>
    <b>Sponsor: Mike Atlas</b><br/>
    <span class="ballot">Status: <span class="status">Passed 8-2, 2 no votes</span></span><br/>
Remove game planning in all forms.
<blockquote><i>Repeal rule V.D<br/>
        Automatically reject any unresolved proposals dealing with game planning including 2018.2, 2018.3, 2018.5, 2018.6 and 2018.7
    </i></blockquote>
</p>


<p>
    <b>Proposal 2018.5 - Limit Game Planning For Same Player</b><br/>
    <b>Sponsor: Richard Lawson</b><br/>
    <span class="ballot">Status: <span class="status">Withdrawn due to 2018.4</span></span><br/>
    Make it so that you can only game plan for the same player a total of three times a season.
<blockquote><i>Add rule V.D.7 reading:
        <blockquote>7. The same player may only be game planned for three times each season</blockquote>
    </i></blockquote>
</p>

<p>
    <b>Proposal 2018.6 - Remove Game Planning Against</b><br/>
    <b>Sponsor: Richard Lawson</b><br/>
    <span class="ballot">Status: <span class="status">Withdrawn due to 2018.4</span></span><br/>
Change game planning to only game plan for your team not against your opponent.
<blockquote><i>Strike the words "and one player on their opponent's team" from rule V.D<br/>
       Repeal rules V.D.2 and V.D.3
    </i></blockquote>
</p>

<p>
    <b>Proposal 2018.7 - Limit Game Planning to the Regular Season</b><br/>
    <b>Sponsor: Richard Lawson</b><br/>
    <span class="ballot">Status: <span class="status">Withdrawn due to 2018.4</span></span><br/>
Remove game planning from all post-season games
<blockquote><i>Replace the word "week" with "regular season game" in rule V.D.
    </i></blockquote>
</p>

<p>
    <b>Proposal 2018.8 - Wild Card not automatically seeded 4th</b><br/>
    <b>Sponsor: Josh Utterback</b><br/>
    <span class="ballot">Status:  <span class="status">Passed 8-3, 1 abstain</span></span><br/>
Seed all four playoff teams by record, with no auto-seeding wild card as 4th.  In tie-breakers give division winners edge.  Remove prohibition on teams from the same division meeting in the playoffs
<blockquote><i>Change rule III.A to read:
    <blockquote>A. Once the four teams that will make the playoffs have been decided, they are seeded based on regular season record<br/>
    &nbsp;&nbsp;&nbsp;&nbsp;1. Seeded will be done based on tie-breaker rules above.<br/>
    &nbsp;&nbsp;&nbsp;&nbsp;2. Excepting that the wild card team will lose any tie-breakers they are involved in</blockquote>
    Repeal rule III.B.3
    </i></blockquote>
</p>

<p>
    <b>Proposal 2018.9 - Update Division winner and First Round Payoffs</b><br/>
    <b>Sponsor: Josh Utterback</b><br/>
    <span class="ballot">Status: <span class="status">Passed 8-1, 3 no votes</span></span><br/>
Adjust the payouts for winning the division and losing in the first round to be 5% each to better reflect three division set-up
<blockquote><i>Replace the percentage in rules XI.C.3.c and XI.C.3.d to be 5%
    </i></blockquote>
</p>

<p>
    <b>Proposal 2018.10 - Update the auto draft algorithm</b><br/>
    <b>Sponsor: Josh Utterback</b><br/>
    <span class="ballot">Status: <span class="status">Passed 8-1, 3 no votes</span></span><br/>
Adjust auto draft to draft starters before backups and a single backup before multiple backups
<blockquote><i>Change rule VI.E.3.b to read:
    <blockquote>b. When the league picks players for a team, it will select starters at every position before selecting backups at any position.<br/>
    &nbsp;&nbsp;&nbsp;&nbsp;i. A backup at every position will be picked before a second backup at any position</blockquote>
    </i></blockquote>
</p>

<? include "base/footer.php"; ?>
