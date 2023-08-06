<?php
// establish connection
require 'utils/start.php';
include_once 'base/useful.php';


$onTeamQuery = 'SELECT s.name as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p, roster r, team s WHERE p.playerid=r.playerid AND r.teamid=s.teamid AND r.dateoff IS NULL ';
$neverTeamQuery = "SELECT 'Available' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p LEFT JOIN roster r ON p.playerid = r.playerid WHERE r.dateon IS NULL ";
$noOnTeamQuery = "SELECT 'Available' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p, roster r WHERE p.playerid = r.playerid ";
//$neverTeamQuery = "SELECT 'Available       ' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p LEFT JOIN roster r ON p.playerid = r.playerid WHERE r.dateon IS NULL ";
//$noOnTeamQuery = "SELECT 'Available       ' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p, roster r WHERE p.playerid = r.playerid ";
$noOnGroupQuery = 'GROUP BY p.playerid HAVING COUNT(r.dateon) = COUNT(r.dateoff) ';

$onroster = 'r.dateoff is null and r.dateon is not null ';
$offroster = 'r.dateoff is not null or t.dateon is null ';
//$whereclause = "AND p.status<>'R' AND p.status<>'O' ";
$whereclause = 'AND p.active=1 AND p.usePos=1 ';

if (!isset($Position)) {
    $Position = '';
}
if (!isset($Team)) {
    $Team = 'ANY';
}
if (!isset($Last)) {
    $Last = '';
}
if (!isset($First)) {
    $First = '';
}
if (!isset($Available)) {
    $Available = '';
}
if (!isset($Order)) {
    $Order = '';
}
$theset = FALSE;

if (isset($submit)) {
    $theset = TRUE;
    // Team is first to allow retired
    if ($Team != '') {
        if ($Team == 'NONE') {
            $whereclause .= "AND (p.team='' OR p.team is null) ";
        } else if ($Team == 'ANY') {
            $whereclause .= "AND p.team<>'' AND p.team is not null ";
        } else if ($Team == 'RET') {
            // don't start with <>'R' for retired
            //$whereclause = "AND p.status='R' ";
            $whereclause = 'AND p.retired is not null ';
        } else {
            $whereclause .= "AND p.team='$Team' ";
        }
    }
    if ($Position != '') {
        $whereclause .= "AND p.pos='$Position' ";
    }
    if ($Last != '') {
        $whereclause .= "AND p.lastname like '%$Last%' ";
    }
    if ($First != '') {
        $whereclause .= "AND p.firstname like '%$First%' ";
    }

    if ($Available == 'available') {
//			$whereclause .= "( $offroster )";
        $search = "( $neverTeamQuery $whereclause ) UNION ( $noOnTeamQuery $whereclause $noOnGroupQuery ) ";
    } else if ($Available == 'taken') {
        $search = $onTeamQuery . $whereclause;
//			$whereclause .= $onroster;
    } else {
        //$search = "( $neverTeamQuery $whereclause ) UNION ( $noOnTeamQuery $whereclause $noOnGroupQuery ) UNION ( $onTeamQuery $whereclause ) ";
        $search = "($neverTeamQuery $whereclause ) UNION  ($noOnTeamQuery $whereclause $noOnGroupQuery)  UNION  ($onTeamQuery $whereclause)  ";
//			$whereclause .= "1=1 ";
    }


    if ($Order == '') $Order = 'lastname';
    $orderby = 'ORDER BY ' . $Order;

    //$result = mysqli_query($conn, "$search where $whereclause $orderby") or die("query");
    //print $search.$orderby;
    $result = mysqli_query($conn, $search . $orderby) or
//        print mysqli_error($conn);
    die("query: $search$orderby<br/>" . mysqli_error($conn));


}

$title = 'WMFFL Players List';
include 'base/menu.php'; ?>

<H1 ALIGN=Center>List Players</H1>
<HR size="1">

<?php
include 'transmenu.php';
if ($isin) {
    ?>
    <div class="container">

        <P>Step 1: Search for players by any of the below criteria and selecting "List Players".
            First and Last Name have assumed wildcards (ie, 'Sm' will give you Smith and Small
            as well as Ismail and Riemersma).</P>

        <P>Step 2: Change the 'Available' tag to 'Pick Up' for any players you are interested in. If you only want to
            drop
            players or change your waiver priorities skip this step.</P>

        <P>Step 3: Click the "Perform Transactions" button to go to the next page.</P>
        <hr />

        <FORM ACTION="list.php" METHOD="POST">

            Last Name:<INPUT TYPE="text" NAME="Last" VALUE="<?php print $Last; ?>">
            First Name:<INPUT TYPE="text" NAME="First" VALUE="<?php print $First; ?>"><BR>

            <SELECT NAME="Position">
                <OPTION VALUE=""<?php if ($Position == '') print ' SELECTED '; ?>>ALL Positions</OPTION>
                <OPTION VALUE="HC"<?php if ($Position == 'HC') print ' SELECTED '; ?>>Head Coaches</OPTION>
                <OPTION VALUE="QB"<?php if ($Position == 'QB') print ' SELECTED '; ?>>Quarterbacks</OPTION>
                <OPTION VALUE="RB"<?php if ($Position == 'RB') print ' SELECTED '; ?>>Runningbacks</OPTION>
                <OPTION VALUE="WR"<?php if ($Position == 'WR') print ' SELECTED '; ?>>Wide Recievers</OPTION>
                <OPTION VALUE="TE"<?php if ($Position == 'TE') print ' SELECTED '; ?>>Tight Ends</OPTION>
                <OPTION VALUE="K"<?php if ($Position == 'K') print ' SELECTED '; ?>>Kickers</OPTION>
                <OPTION VALUE="OL"<?php if ($Position == 'OL') print ' SELECTED '; ?>>Offensive Lines</OPTION>
                <OPTION VALUE="DL"<?php if ($Position == 'DL') print ' SELECTED '; ?>>Defensive Lines</OPTION>
                <OPTION VALUE="LB"<?php if ($Position == 'LB') print ' SELECTED '; ?>>Linebackers</OPTION>
                <OPTION VALUE="DB"<?php if ($Position == 'DB') print ' SELECTED '; ?>>Defensive Backs</OPTION>
            </SELECT>

            <SELECT NAME="Team">
                <OPTION VALUE=""<?php if ($Team == '') print ' SELECTED '; ?>>ALL NFL Teams</OPTION>
                <OPTION VALUE="ANY"<?php if ($Team == 'ANY') print ' SELECTED '; ?>>Any</OPTION>
                <OPTION VALUE="NONE"<?php if ($Team == 'NONE') print ' SELECTED '; ?>>None</OPTION>
                <OPTION VALUE="RET"<?php if ($Team == 'RET') print ' SELECTED '; ?>>Retired</OPTION>
                <OPTION VALUE="ARI"<?php if ($Team == 'ARI') print ' SELECTED '; ?>>Arizona</OPTION>
                <OPTION VALUE="ATL"<?php if ($Team == 'ATL') print ' SELECTED '; ?>>Atlanta</OPTION>
                <OPTION VALUE="BAL"<?php if ($Team == 'BAL') print ' SELECTED '; ?>>Baltimore</OPTION>
                <OPTION VALUE="BUF"<?php if ($Team == 'BUF') print ' SELECTED '; ?>>Buffalo</OPTION>
                <OPTION VALUE="CAR"<?php if ($Team == 'CAR') print ' SELECTED '; ?>>Carolina</OPTION>
                <OPTION VALUE="CHI"<?php if ($Team == 'CHI') print ' SELECTED '; ?>>Chicago</OPTION>
                <OPTION VALUE="CIN"<?php if ($Team == 'CIN') print ' SELECTED '; ?>>Cincinnati</OPTION>
                <OPTION VALUE="CLE"<?php if ($Team == 'CLE') print ' SELECTED '; ?>>Cleveland</OPTION>
                <OPTION VALUE="DAL"<?php if ($Team == 'DAL') print ' SELECTED '; ?>>Cowgirls</OPTION>
                <OPTION VALUE="DEN"<?php if ($Team == 'DEN') print ' SELECTED '; ?>>Denver</OPTION>
                <OPTION VALUE="DET"<?php if ($Team == 'DET') print ' SELECTED '; ?>>Detroit</OPTION>
                <OPTION VALUE="GB"<?php if ($Team == 'GB') print ' SELECTED '; ?>>Green Bay</OPTION>
                <OPTION VALUE="IND"<?php if ($Team == 'IND') print ' SELECTED '; ?>>Indianapolis</OPTION>
                <OPTION VALUE="HOU"<?php if ($Team == 'HOU') print ' SELECTED '; ?>>Houston</OPTION>
                <OPTION VALUE="JAC"<?php if ($Team == 'JAC') print ' SELECTED '; ?>>Jacksonville</OPTION>
                <OPTION VALUE="KC"<?php if ($Team == 'KC') print ' SELECTED '; ?>>Kansas City</OPTION>
                <OPTION VALUE="LV"<?php if ($Team == 'LV') print ' SELECTED '; ?>>Las Vegas</OPTION>
                <OPTION VALUE="LAC"<?php if ($Team == 'LAC') print ' SELECTED '; ?>>LA Chargers</OPTION>
                <OPTION VALUE="LAR"<?php if ($Team == 'LAR') print ' SELECTED '; ?>>LA Rams</OPTION>
                <OPTION VALUE="MIA"<?php if ($Team == 'MIA') print ' SELECTED '; ?>>Miami</OPTION>
                <OPTION VALUE="MIN"<?php if ($Team == 'MIN') print ' SELECTED '; ?>>Minnesota</OPTION>
                <OPTION VALUE="NE"<?php if ($Team == 'NE') print ' SELECTED '; ?>>New England</OPTION>
                <OPTION VALUE="NO"<?php if ($Team == 'NO') print ' SELECTED '; ?>>New Orleans</OPTION>
                <OPTION VALUE="NYG"<?php if ($Team == 'NYG') print ' SELECTED '; ?>>New York Giants</OPTION>
                <OPTION VALUE="NYJ"<?php if ($Team == 'NYJ') print ' SELECTED '; ?>>New York Jets</OPTION>
                <OPTION VALUE="PHI"<?php if ($Team == 'PHI') print ' SELECTED '; ?>>Philadelphia</OPTION>
                <OPTION VALUE="PIT"<?php if ($Team == 'PIT') print ' SELECTED '; ?>>Pittsburgh</OPTION>
                <OPTION VALUE="SEA"<?php if ($Team == 'SEA') print ' SELECTED '; ?>>Seattle</OPTION>
                <OPTION VALUE="SF"<?php if ($Team == 'SF') print ' SELECTED '; ?>>San Francisco</OPTION>
                <OPTION VALUE="TB"<?php if ($Team == 'TB') print ' SELECTED '; ?>>Tampa Bay</OPTION>
                <OPTION VALUE="TEN"<?php if ($Team == 'TEN') print ' SELECTED '; ?>>Tennessee</OPTION>
                <OPTION VALUE="WAS"<?php if ($Team == 'WAS') print ' SELECTED '; ?>>Washington</OPTION>
            </SELECT>

            <SELECT NAME="Available">
                <OPTION VALUE="" <?php if ($Available == '') print 'SELECTED '; ?>>ALL Players</OPTION>
                <OPTION VALUE="available" <?php if ($Available == 'available') print 'SELECTED '; ?>>Available Players
                </OPTION>
                <OPTION VALUE="taken" <?php if ($Available == 'taken') print 'SELECTED '; ?>>Taken Players</OPTION>
            </SELECT>

            <INPUT TYPE="Hidden" NAME="Order" VALUE="<?php print $Order; ?>">
            <INPUT TYPE="Submit" NAME="submit" VALUE="List Players">
        </FORM>

        <TABLE>
            <FORM ACTION="confirm.php" METHOD="POST">
                <TR>
                    <TD>
                        <B><A HREF="list.php?Position=<?php print $Position; ?>&Team=<?php print $Team; ?>&Available=<?php print $Available; ?>&Order=teamname&submit=y&Last=<?php print $Last; ?>&First=<?php print $First; ?>">Current
                                Team</A></B></TD>
                    <TD>&nbsp;&nbsp;&nbsp;</TD>
                    <TD>
                        <B><A HREF="list.php?Position=<?php print $Position; ?>&Team=<?php print $Team; ?>&Available=<?php print $Available; ?>&Order=lastname&submit=y&Last=<?php print $Last; ?>&First=<?php print $First; ?>">Last
                                Name</A></B></TD>
                    <TD>&nbsp;&nbsp;&nbsp;</TD>
                    <TD>
                        <B><A HREF="list.php?Position=<?php print $Position; ?>&Team=<?php print $Team; ?>&Available=<?php print $Available; ?>&Order=firstname&submit=y&Last=<?php print $Last; ?>&First=<?php print $First; ?>">First
                                Name</A></B></TD>
                    <TD>&nbsp;&nbsp;&nbsp;</TD>
                    <TD>
                        <B><A HREF="list.php?Position=<?php print $Position; ?>&Team=<?php print $Team; ?>&Available=<?php print $Available; ?>&Order=position&submit=y&Last=<?php print $Last; ?>&First=<?php print $First; ?>">Pos</A></B>
                    </TD>
                    <TD>&nbsp;&nbsp;&nbsp;</TD>
                    <TD>
                        <B><A HREF="list.php?Position=<?php print $Position; ?>&Team=<?php print $Team; ?>&Available=<?php print $Available; ?>&Order=nflteam&submit=y&Last=<?php print $Last; ?>&First=<?php print $First; ?>">NFL
                                Team</A></B></TD>
                </TR>
                <?php
                if ($theset) {
                    while (list($avail, $last, $first, $pos, $team, $id) = mysqli_fetch_row($result)) {
                        if ($avail == 'Available') {
                            print "<TR><TD><SELECT NAME=\"pick$id\"><OPTION VALUE=\"\" SELECTED>Available</OPTION><OPTION VALUE=\"$id\">Pick Up</OPTION></SELECT></TD>";
                        } else {
                            print "<TR><TD>$avail</TD>";
                        }
                        print "<TD>&nbsp;&nbsp;&nbsp;</TD><TD>$last</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD>$first</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD>$pos</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD>$team</TD></TR>";
                    }
                }

                ?>
                <TR>
                    <TD COLSPAN=9 ALIGN=Center><INPUT TYPE="Submit" NAME="submit" VALUE="Perform Transactions"></TD>
                </TR>
            </FORM>
        </TABLE>
    </div>

    <?php
} else {
    ?>

    <div class="text-center font-weight-bold h4">You must be logged in to use this feature</div>

<?php }
include 'base/footer.php';
?>
