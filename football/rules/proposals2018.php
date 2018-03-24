<?
session_start();
$title = "WMFFL Rule Proposals";

$cssList = array("rules.css");
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
<span class="ballot">Status: <span class="status">In Discussion</span></span><br/>
Add a home field advantage that will be small, maybe 2 points.  There would be a number of factors that can adjust that
up or down.  If the team is on a winning streak the points would be higher and would be lower for a losing streak.  A team
in first place would get a slight bump as well.  The exact details will be worked out later.
<blockquote><i>Details TBD</i></blockquote>
</p>

<p>
<b>Proposal 2018.2 - Adjust Game Plan For to 50%</b><br/>
<b>Sponsor: Josh Utterback</b><br/>
<span class="ballot">Status: <span class="status">In Discussion</span></span><br/>
Game planning for scores too many points.  Adjust it to match the Game Plan Against score, which is 50% of the base score.
<blockquote><i>Update rule V.D.1 to read:
        <blockquote>1. The player on their team that is game planned for will have their score increased by 50%, with fractions rounded down.<br/>
        </blockquote>
    </i></blockquote>
</p>

<p>
    <b>Proposal 2018.3 - Negative Game Plan For Increase Not Decrease</b><br/>
    <b>Sponsor: Brian Elliff</b><br/>
    <span class="ballot">Status: <span class="status">In Discussion</span></span><br/>
A game planned for player that scores negative has their score doubled (from -8 to -16).  Reverse that and half it.
<blockquote><i>Add rule V.D.1.a to read:
        <blockquote>a. If a game planned for player scores negative their score will be halved, not doubled.<br/>
        </blockquote>
    </i></blockquote>
</p>


<p>
    <b>Proposal 2018.4 - Remove Game Planning</b><br/>
    <b>Sponsor: Mike Atlas</b><br/>
    <span class="ballot">Status: <span class="status">In Discussion</span></span><br/>
Remove game planning in all forms.
<blockquote><i>Repeal rule V.D<br/>
        Automatically reject any unresolved proposals dealing with game planning including 2018.2, 2018.3, 2018.5, 2018.6 and 2018.7
    </i></blockquote>
</p>


<p>
    <b>Proposal 2018.5 - Limit Game Planning For Same Player</b><br/>
    <b>Sponsor: Richard Lawson</b><br/>
    <span class="ballot">Status: <span class="status">In Discussion</span></span><br/>
    Make it so that you can only game plan for the same player a total of three times a season.
<blockquote><i>Add rule V.D.7 reading:
        <blockquote>7. The same player may only be game planned for three times each season</blockquote>
    </i></blockquote>
</p>

<p>
    <b>Proposal 2018.6 - Remove Game Planning Against</b><br/>
    <b>Sponsor: Richard Lawson</b><br/>
    <span class="ballot">Status: <span class="status">In Discussion</span></span><br/>
Change game planning to only game plan for your team not against your opponent.
<blockquote><i>Strike the words "and one player on their opponent's team" from rule V.D<br/>
       Repeal rules V.D.2 and V.D.3
    </i></blockquote>
</p>

<p>
    <b>Proposal 2018.7 - Limit Game Planning to the Regular Season</b><br/>
    <b>Sponsor: Richard Lawson</b><br/>
    <span class="ballot">Status: <span class="status">In Discussion</span></span><br/>
Remove game planning from all post-season games
<blockquote><i>Replace the word "week" with "regular season game" in rule V.D.
    </i></blockquote>
</p>
<? include "base/footer.html"; ?>
