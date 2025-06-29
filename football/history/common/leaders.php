<?php
/**
 * @var $conn mysqli
 * @var $thisSeason int
 */
require_once 'utils/start.php'; // Defines $conn, $thisSeason

$season = isset($_REQUEST['season']) ? (int)$_REQUEST['season'] : $thisSeason;

// SQL to get total points scored by each team for each activated position slot
// It sums up all points a team achieved from players in a specific slot (e.g., QB, RB1, WR1)
$sql = 'SELECT t.name AS team_name, ra.pos AS position_slot, SUM(ps.active) AS total_points
        FROM playerscores ps
        JOIN newplayers p ON ps.playerid = p.playerid
        JOIN revisedactivations ra ON ra.playerid = ps.playerid AND ps.season = ra.season AND ps.week = ra.week
        JOIN teamnames t ON ra.teamid = t.teamid AND ps.season = t.season
        WHERE ps.season = ? AND ps.week <= 14 AND ps.active IS NOT NULL
        GROUP BY t.name, ra.pos
        ORDER BY ra.pos, total_points DESC';

// Use prepared statements
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die('MySQLi prepare failed: ' . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, 'i', $season);
mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);
if (!$results) {
    die('MySQLi get_result failed: ' . mysqli_stmt_error($stmt));
}

// Fetching max week (original script had this, might be used for display context)
$maxWeek = $season; // Default to season if no scores yet
$dateQuery = 'SELECT MAX(week) FROM playerscores WHERE season = ? AND week <= 14';
$dateStmt = mysqli_prepare($conn, $dateQuery);
if ($dateStmt) {
    mysqli_stmt_bind_param($dateStmt, 'i', $season);
    mysqli_stmt_execute($dateStmt);
    $dateRes = mysqli_stmt_get_result($dateStmt);
    if ($dateRes && mysqli_num_rows($dateRes) > 0) {
        list($currentWeek) = mysqli_fetch_row($dateRes);
        $maxWeek = $currentWeek ?: $season; // Use fetched week, or season if null
    }
    mysqli_stmt_close($dateStmt);
}


// Group data by position slot
$leadersByPosition = [];
while ($row = mysqli_fetch_assoc($results)) {
    $positionSlot = htmlspecialchars($row['position_slot'], ENT_QUOTES, 'UTF-8');
    // team_name can be null if a teamid in revisedactivations doesn't map to teamnames for that season
    $teamName = isset($row['team_name']) ? htmlspecialchars($row['team_name'], ENT_QUOTES, 'UTF-8') : 'Unknown Team';
    $totalPoints = (int)$row['total_points'];

    if (!isset($leadersByPosition[$positionSlot])) {
        $leadersByPosition[$positionSlot] = [];
    }
    // Store team and their points for this slot
    $leadersByPosition[$positionSlot][] = ['team_name' => $teamName, 'points' => $totalPoints];
}
mysqli_stmt_close($stmt);

// Aggregate total offensive and defensive points for each TEAM
$teamOffensiveTotals = [];
$teamDefensiveTotals = [];
$allTeamNames = [];

foreach ($leadersByPosition as $positionSlot => $teamsInSlot) {
    foreach ($teamsInSlot as $teamData) {
        $teamName = $teamData['team_name'];
        $points = $teamData['points'];

        if (!isset($teamOffensiveTotals[$teamName])) $teamOffensiveTotals[$teamName] = 0;
        if (!isset($teamDefensiveTotals[$teamName])) $teamDefensiveTotals[$teamName] = 0;
        if (!in_array($teamName, $allTeamNames, true) && $teamName !== 'Unknown Team') {
            $allTeamNames[] = $teamName;
        }

        $defensivePositionPatterns = ['DB', 'LB', 'DL']; // Base defensive position types
        $isDefensiveSlot = false;
        foreach ($defensivePositionPatterns as $defPattern) {
            if (str_starts_with($positionSlot, $defPattern)) { // Handles DL, DL1, DB, DB1 etc.
                $isDefensiveSlot = true;
                break;
            }
        }

        if ($isDefensiveSlot) {
            $teamDefensiveTotals[$teamName] += $points;
        } else {
            $teamOffensiveTotals[$teamName] += $points;
        }
    }
}

arsort($teamOffensiveTotals);
arsort($teamDefensiveTotals);

$teamOverallTotals = [];
foreach ($allTeamNames as $teamName) {
    $offScore = $teamOffensiveTotals[$teamName] ?? 0;
    $defScore = $teamDefensiveTotals[$teamName] ?? 0;
    $teamOverallTotals[$teamName] = $offScore + $defScore;
}
arsort($teamOverallTotals);

$numCol = 3; // Number of columns for position leaders display
$title = "$season League Leaders (Week $maxWeek)"; // More descriptive title
?>

<?php include 'base/menu.php'; ?>

    <H1 ALIGN=Center><?= htmlspecialchars($season, ENT_QUOTES, 'UTF-8') ?> League Leaders</H1>
    <P ALIGN=Center><I>(Through Week <?= htmlspecialchars($maxWeek, ENT_QUOTES, 'UTF-8') ?>)</I></P>
    <HR>

    <TABLE WIDTH="100%" ALIGN="Center" CELLPADDING="5" CELLSPACING="0">
        <TR>
            <?php
            $itemCount = 0;
            // ksort($leadersByPosition); // Optional: Sort position slots alphabetically if desired for display order
            foreach ($leadersByPosition as $positionSlot => $teams) :
                if ($itemCount > 0 && $itemCount % $numCol == 0) {
                    echo '</TR><TR><TD COLSPAN="' . $numCol . '" STYLE="height:20px;">&nbsp;</TD></TR><TR>'; // Spacer row
                }
                $itemCount++;
                ?>
                <TD valign="top" ALIGN="Center" WIDTH="<?= round(100/$numCol) ?>%">
                    <TABLE BORDER="0" CELLPADDING="3" CELLSPACING="0" WIDTH="90%">
                        <TR><TH COLSPAN="2" ALIGN="Center"><?= $positionSlot ?></TH></TR>
                        <TR><TH ALIGN="Left">Team</TH><TH ALIGN="Right">Points</TH></TR>
                        <?php
                        $teamsDisplayedCount = 0;
                        if (!empty($teams)) {
                            foreach ($teams as $teamData) :
                                if ($teamsDisplayedCount >= 12) break; // Show top 12 per slot
                                ?>
                                <TR>
                                    <TD><?= $teamData['team_name'] ?></TD>
                                    <TD ALIGN="Right"><?= $teamData['points'] ?></TD>
                                </TR>
                                <?php
                                $teamsDisplayedCount++;
                            endforeach;
                            if ($teamsDisplayedCount === 0) {
                                echo '<TR><TD COLSPAN="2" ALIGN="Center">No data</TD></TR>';
                            }
                         } else {
                            echo '<TR><TD COLSPAN="2" ALIGN="Center">No data</TD></TR>';
                        }
                        ?>
                    </TABLE>
                </TD>
            <?php endforeach; ?>
            <?php
            // Fill remaining cells in the last row if it's not full
            if ($itemCount > 0 && $itemCount % $numCol != 0) {
                for ($i = 0; $i < ($numCol - ($itemCount % $numCol)); $i++) {
                    echo "<TD WIDTH=\"" . round(100/$numCol) . "%\">&nbsp;</TD>";
                }
            }
            ?>
        </TR>

        <!-- Summary Tables Section -->
        <TR><TD COLSPAN="<?= $numCol ?>" STYLE="height:30px;">&nbsp;</TD></TR> <!-- Spacer row -->
        <TR>
            <TD COLSPAN="<?= $numCol ?>" ALIGN="Center">
                <TABLE BORDER="0" CELLSPACING="20" CELLPADDING="0"> <!-- Outer table for side-by-side summaries -->
                    <TR>
                        <TD VALIGN="top">
                            <TABLE BORDER="0" CELLPADDING="3" CELLSPACING="0" WIDTH="250">
                                <TR><TH COLSPAN="2" ALIGN="Center">Team Offensive Totals</TH></TR>
                                <TR><TH ALIGN="Left">Team</TH><TH ALIGN="Right">Points</TH></TR>
                                <?php if (!empty($teamOffensiveTotals)):
                                    foreach ($teamOffensiveTotals as $team => $score) : ?>
                                        <TR><TD><?= $team ?></TD><TD ALIGN="Right"><?= $score ?></TD></TR>
                                    <?php endforeach; else: ?>
                                    <TR><TD COLSPAN="2" ALIGN="Center">No data</TD></TR>
                                <?php endif; ?>
                            </TABLE>
                        </TD>
                        <TD VALIGN="top">
                            <TABLE BORDER="1" CELLPADDING="3" CELLSPACING="0" WIDTH="250">
                                <TR><TH COLSPAN="2" ALIGN="Center">Team Defensive Totals</TH></TR>
                                <TR><TH ALIGN="Left">Team</TH><TH ALIGN="Right">Points</TH></TR>
                                <?php if (!empty($teamDefensiveTotals)):
                                    foreach ($teamDefensiveTotals as $team => $score) : ?>
                                        <TR><TD><?= $team ?></TD><TD ALIGN="Right"><?= $score ?></TD></TR>
                                    <?php endforeach; else: ?>
                                    <TR><TD COLSPAN="2" ALIGN="Center">No data</TD></TR>
                                <?php endif; ?>
                            </TABLE>
                        </TD>
                        <TD VALIGN="top">
                            <TABLE BORDER="1" CELLPADDING="3" CELLSPACING="0" WIDTH="250">
                                <TR><TH COLSPAN="2" ALIGN="Center">Team Overall Totals</TH></TR>
                                <TR><TH ALIGN="Left">Team</TH><TH ALIGN="Right">Points</TH></TR>
                                <?php if (!empty($teamOverallTotals)):
                                    foreach ($teamOverallTotals as $team => $score) : ?>
                                        <TR><TD><?= $team ?></TD><TD ALIGN="Right"><?= $score ?></TD></TR>
                                    <?php endforeach; else: ?>
                                    <TR><TD COLSPAN="2" ALIGN="Center">No data</TD></TR>
                                <?php endif; ?>
                            </TABLE>
                        </TD>
                    </TR>
                </TABLE>
            </TD>
        </TR>
    </TABLE>

<?php
include 'base/footer.php';
?>