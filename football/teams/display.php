<?php

// football/teams/history/display.php

/**
 * Displays the history table.
 *
 * @param array $seasonArray The array of season data.
 * @return void
 */
function displayHistoryTable(array $seasonArray): void
{
    print <<<EOD
        <A NAME="History"><H4 class="font-weight-bold">History</H4></A>
        <TABLE WIDTH=75%>
        <TH>Record
        <TR><TD WIDTH=20%><B>YEAR</B></TD><TD WIDTH=8%><B>W</B></TD><TD WIDTH=8%><B>L</B></TD>
        <TD WIDTH=4%><B>T</B></TD><TD WIDTH=10%><B>PCT</B></TD><TD WIDTH=50%><B>FINISH</B></TD></TR>
    EOD;
    $count = 0;
    foreach ($seasonArray as $innerArray) {
        print '<tr>';
        if ($count == 0) {
            $startLabel = '<b>';
            $endLabel = '</b>';
            $count++;
        } else {
            $startLabel = '';
            $endLabel = '';
        }
        foreach ($innerArray as $item) {
            print "<td>$startLabel$item$endLabel</td>";
        }
        print '</tr>';
    }
    print '</table>';
}

/**
 * Displays the playoff results table.
 *
 * @param array $playoffResults The array of playoff game results.
 * @return void
 */
function displayPlayoffResultsTable(array $playoffResults): void
{
    print '<TABLE width=75%><TH>PostSeason Results</th>';
    foreach ($playoffResults as $singleGame) {
        print "<tr><td>$singleGame[0]</td>";
        print "<td>$singleGame[1]</td>";
        print "<td>$singleGame[2]</td></tr>";
    }
    print '</TABLE>';
}

/**
 * Displays the division and league titles tables.
 *
 * @param array $divisionTitles The array of division titles.
 * @param array $leagueTitles   The array of league titles.
 * @return void
 */
function displayTitlesTables(array $divisionTitles, array $leagueTitles): void
{
    print <<<EOD
        <TABLE ALIGN=Left>
        <TH>Division Titles</th>
    EOD;
    foreach ($divisionTitles as $divTitle) {
        $season = $divTitle[0];
        $divName = $divTitle[1];
        print "<TR><TD>$season</TD><TD>$divName Title</TD></TR>";
    }
    print '</TABLE></TD>';

    print <<<EOD
    <TD WIDTH=50% VALIGN=Top>
        <TABLE>
        <TH>League Titles</th>
    EOD;
    foreach ($leagueTitles as $legTitle) {
        print "<TR><TD>$legTitle</TD><TD>WMFFL Champions</TD></TR>";
    }
    print '</TABLE>';
}

/**
 * Displays the past owners table.
 *
 * @param array $pastArray The array of past owner data.
 * @param string $display
 * @return void
 */
function displayPastTable(array $pastArray, string $display): void
{
    print <<<EOD
        <table><tr>
        <th>Past $display</th>
    EOD;
    foreach ($pastArray as $owners) {
        $startDate = $owners['start'];
        $endDate = $owners['end'];
        if ($endDate == 0) {
            $endDate = '';
        }
        $ownerName = $owners['name'];
        print '<tr>';
        print "<td>$startDate";
        print ($endDate != '' ? "-$endDate" : '');
        print "</td><td>$ownerName</td></tr>";

    }
    print '</table>'; // This was the missing closing tag
}

