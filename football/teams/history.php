<?php
// football/teams/history/history.php
/**
 * @var $currentWeek int
 * @var $currentSeason int
 * @var $conn mysqli
 */

// Include necessary files
require_once 'utils/start.php'; // Adjust path if needed
require_once 'dataRetrieval.php';
require_once 'dataFormatting.php';
require_once 'display.php';

// Get the team ID from the request
if (!isset($viewteam)) {
    // Handle error or redirect
    echo 'Error: Team ID not provided.';
    exit;
}

// Retrieve data using the new modules
$playoffRecord = getPlayoffRecord($viewteam, $conn);
$regularSeasonRecords = getRegularSeasonRecords($viewteam, $currentWeek, $currentSeason, $conn);
$allTimeRecord = getAllTimeRecord($regularSeasonRecords);

$seasonArray = [$allTimeRecord];
foreach ($playoffRecord as $row) {
    $seasonArray[] = $row;
}
$seasonArray = array_merge($seasonArray, $regularSeasonRecords);

$playoffResults = getPlayoffResults($viewteam, $conn);
list($leagueTitles, $divisionTitles) = getTitles($viewteam, $conn);
$pastNames = getPastNames($viewteam, $conn);
$pastOwners = getPastOwners($viewteam, $conn);

// Display the data using the new modules
print "<table width='100%'>";
print '<tr><td align=center colspan=2>';
displayHistoryTable($seasonArray);
print '</td></tr>';
print '<tr><td align=Center colspan=2>';
displayPlayoffResultsTable($playoffResults);
print '</td></tr>';
print '<tr><td width="50%" valign="Top">';
displayTitlesTables($divisionTitles, $leagueTitles);
print '</td></tr>';

print '<tr><td width=50% valign=top>';
displayPastTable($pastOwners, 'Owners');
print '</td><td width=50% valign=top>';
displayPastTable($pastNames, 'Names');
print '</td></TR>';
print '</table>';
