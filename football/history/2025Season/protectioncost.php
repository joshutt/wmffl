<?php
/**
 * @var $conn mysqli
 */
require_once 'utils/connect.php'; // Assumes $conn is established here

$query = "SELECT p.firstname, p.lastname, pc.years, CEILING(if(p.pos in ('QB', 'RB', 'WR', 'TE'), pc.years, pc.years/2)) as 'Extra', t.name, p.pos ";
$query .= 'FROM newplayers p ';
$query .= 'JOIN protectioncost pc ON p.playerid=pc.playerid ';
$query .= 'LEFT JOIN roster r ON r.playerid=p.playerid AND r.dateoff is null ';
$query .= 'LEFT JOIN team t on r.teamid=t.teamid ';
$query .= 'WHERE pc.season=2025 ';
// It's good practice to include all non-aggregated selected columns from the SELECT list (that are not functionally dependent on the GROUP BY columns) in the GROUP BY clause.
// Assuming p.playerid is unique enough to determine p.firstname, p.lastname, p.pos, and pc.years for a given season.
// t.name is the one that can vary or be NULL due to the LEFT JOIN.
$query .= 'GROUP BY p.playerid, p.firstname, p.lastname, pc.years, p.pos, t.name, Extra '; // Added Extra as it's calculated
$query .= 'ORDER BY t.name, Extra desc, pc.years desc';

$base = array('HC' => 0, 'QB' => 10, 'RB' => 15, 'WR' => 13, 'TE' => 4, 'K' => 1, 'OL' => 1, 'DL' => 3, 'LB' => 5, 'DB' => 4);

$result = mysqli_query($conn, $query) or die('error: ' . mysqli_error($conn));
$count = mysqli_num_rows($result); // Define $count BEFORE the loop

$page = array();
$countall = array();

while ($aLine = mysqli_fetch_array($result)) {
    // Handle cases where t.name might be NULL (player not on a team)
    $teamKey = $aLine['name'] ?? ''; // Default to empty string if NULL

    // Ensure the array elements are initialized before appending or incrementing
    if (!isset($page[$teamKey])) {
        $page[$teamKey] = '';
    }
    if (!isset($countall[$teamKey])) {
        $countall[$teamKey] = 0;
    }

    // Defensive check for $aLine['pos'] before accessing $base
    $positionBaseCost = 0;
    if (isset($aLine['pos']) && array_key_exists($aLine['pos'], $base)) {
        $positionBaseCost = $base[$aLine['pos']];
    }

    $extraCost = (int)($aLine['Extra'] ?? 0); // Ensure Extra is numeric
    $totCost = $positionBaseCost + $extraCost;

    // Escape output for HTML
    $firstName = htmlspecialchars($aLine['firstname'] ?? '', ENT_QUOTES, 'UTF-8');
    $lastName = htmlspecialchars($aLine['lastname'] ?? '', ENT_QUOTES, 'UTF-8');
    $posDisplay = htmlspecialchars($aLine['pos'] ?? '', ENT_QUOTES, 'UTF-8');
    $yearsDisplay = htmlspecialchars($aLine['years'] ?? '', ENT_QUOTES, 'UTF-8');
    $extraDisplay = htmlspecialchars($aLine['Extra'] ?? '', ENT_QUOTES, 'UTF-8');

    // Build the HTML string for the player
    $page[$teamKey] .= "<TR><TD>$firstName $lastName</TD>";
    $page[$teamKey] .= "<TD ALIGN=Center>$posDisplay</TD>";
    $page[$teamKey] .= "<TD ALIGN=Center>$yearsDisplay</TD>";
    $page[$teamKey] .= "<TD ALIGN=Center>+$extraDisplay</TD>";
    $page[$teamKey] .= "<TD ALIGN=Center>$totCost</TD></TR>";

    $countall[$teamKey]++;
}

$title = '2025 Protection Costs';
include 'base/menu.php';
?>

    <H1 Align=Center>Protection Costs</H1>
    <HR size="1">

    <div class="container text-center">
        <p>Any player not listed on the chart below will have a protection cost equal to their position's base:</p>
        <div class="row justify-content-around">
            <TABLE BORDER=1 class="text-center my-2"> <!-- Consider using CSS classes for styling over BORDER attribute -->
                <TR>
                    <?php
                    // Define which positions to show in this header table
                    $headerPositions = ['QB', 'RB', 'WR', 'TE', 'K', 'OL', 'DL', 'LB', 'DB'];
                    foreach ($headerPositions as $posKey): ?>
                        <TD><?= htmlspecialchars($posKey) ?></TD>
                    <?php endforeach; ?>
                </TR>
                <TR>
                    <?php foreach ($headerPositions as $posKey): ?>
                        <TD><?= htmlspecialchars($base[$posKey] ?? 'N/A') ?></TD>
                    <?php endforeach; ?>
                </TR>
            </TABLE>
        </div>
    </div>

    <TABLE WIDTH="100%">
        <TR>
            <TD WIDTH="50%" VALIGN=Top>
                <TABLE ALIGN="Center">
                    <?php
                    $sumup = 0;
                    $teamsDisplayedCount = 0;

                    // Determine which teams go into the first column
                    // Exclude the 'Not on a Roster' (empty key) for this calculation if it exists
                    $teamsToDisplay = array_keys($page);
                    if (isset($page[''])) {
                        $teamsToDisplay = array_filter($teamsToDisplay, function($key) { return $key !== ''; });
                    }
                    $halfwayCount = ceil(($count + count($teamsToDisplay)*3) / 2);

                    foreach ($page as $teamNameKey => $val) {
                        if ($teamNameKey === '') continue; // Skip 'Not on a Roster' for now, will be added at the end

                        if ($sumup >= $halfwayCount) {
                            break; // Move to the second column
                        }
                        ?>
                        <TR>
                            <TH COLSPAN=5><?= htmlspecialchars($teamNameKey); ?></TH>
                        </TR>
                        <TR>
                            <TH>Player Name</TH>
                            <TH>Pos</TH>
                            <TH>Years</TH>
                            <TH>Extra</TH>
                            <th>Total Cost</th>
                        </TR>
                        <?= $val; // $val is pre-formatted HTML, no need to escape here ?>
                        <TR>
                            <TD COLSPAN=5>&nbsp;</TD>
                        </TR>
                        <?php
                        $sumup += ($countall[$teamNameKey] ?? 0) + 3; // Original logic for sumup
                        $teamsDisplayedCount++;
                    }
                    ?>
                </TABLE>
            </TD>
            <TD WIDTH="*">&nbsp;</TD> <!-- Spacer column -->
            <TD WIDTH="50%" VALIGN=Top>
                <TABLE ALIGN="Center" VALIGN=Top>
                    <?php
                    $teamsDisplayedCountInSecondCol = 0;
                    foreach ($page as $teamNameKey => $val) {
                        if ($teamNameKey === '') continue; // Skip 'Not on a Roster' for now

                        // Only display teams that were not in the first column
                        if ($teamsDisplayedCountInSecondCol < $teamsDisplayedCount) {
                            $teamsDisplayedCountInSecondCol++;
                            continue;
                        }
                        ?>
                        <TR>
                            <TH COLSPAN=5><?= htmlspecialchars($teamNameKey); ?></TH>
                        </TR>
                        <TR>
                            <TH>Player Name</TH>
                            <TH>Pos</TH>
                            <TH>Years</TH>
                            <TH>Extra</TH>
                            <th>Total Cost</th>
                        </TR>
                        <?= $val; ?>
                        <TR>
                            <TD COLSPAN=5>&nbsp;</TD>
                        </TR>
                        <?php
                    }

                    // Display "Not on a Roster" section if it exists
                    if (isset($page[''])) {
                        ?>
                        <TR>
                            <TH COLSPAN=5>Not on a Roster</TH>
                        </TR>
                        <TR>
                            <TH>Player Name</TH>
                            <TH>Pos</TH>
                            <TH>Years</TH>
                            <TH>Extra</TH>
                            <th>Total Cost</th>
                        </TR>
                        <?= $page['']; ?>
                        <TR>
                            <TD COLSPAN=5>&nbsp;</TD>
                        </TR>
                    <?php } ?>
                </TABLE>
            </TD>
        </TR>
    </TABLE>

<?php include 'base/footer.php'; ?>