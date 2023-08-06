<?
require_once "utils/start.php";

$title = "WMFFL Ballot";

include "base/menu.php";
?>

<H1 ALIGN=Center>Ballot</H1>
<HR size="1">

<div class="container">

<?
if ($isin) {

    if (isset($_REQUEST["submit"])) {
        include "ballotcount.php";
    } else {
    ?>

    <P>
        For each of the ballot items below your current vote, please select your vote,
        then press the "VOTE" button to have you vote counted. To review the issues in question you may go to the <A
                HREF="/rules/proposals2022.php">proposals page</A>
    </P>



        <FORM ACTION="ballot" METHOD=POST>
            <?

            $thequery = "select i.issueid, i.issuenum, i.issuename, b.vote, i.description
				from issues i, ballot b
				where i.issueid=b.issueid
				and i.startDate<=now() 
				and (Deadline is null or Deadline >= now())
				and b.teamid=" . $teamnum . " order by issuenum";

            $results = mysqli_query($conn, $thequery);
            while (list($issueid, $issuenum, $issuename, $vote, $descr) = mysqli_fetch_row($results)) {

                $accept = "Accept";
                $reject = "Reject";
                $abstain = "Abstain";
                $votelabel = $vote;
                if ($issueid == 87) {
                    $accept = "10 Teams";
                    $reject = "12 Teams";
                    $abstain = "No Preference";
                    switch ($vote) {
                        case "Accept" :
                            $votelabel = $accept;
                            break;
                        case "Reject" :
                            $votelabel = $reject;
                            break;
                        case "Abstain" :
                            $votelabel = $abstain;
                            break;
                    }
                }
                ?>
<div class="card m-3 p-2 bg-light">
    <div class="card-header">
    <b>Proposal <?= $issuenum; ?> - <?= $issuename ?></b>
    </div>
    <div class="card-body">
    <p><?= $descr; ?></p>
    <p><i>
                            <?php if ($vote != "") {
                                print "Your current vote is to $votelabel this proposal";
                                //if ($vote == "1") print "approve this proposal";
                                //else print "reject this proposal";
                            } else {
                                print "You have not voted on this proposal";
                            }
                            ?></i></p>
<p><INPUT TYPE="radio" NAME="<?= $issueid; ?>" VALUE="Accept" <? if ($vote == "Accept") print "CHECKED"; ?>> <?= $accept; ?></p>
<p><INPUT TYPE="radio" NAME="<?= $issueid; ?>" VALUE="Reject" <? if ($vote == "Reject") print "CHECKED"; ?>> <?= $reject; ?></p>
<p><INPUT TYPE="radio" NAME="<?= $issueid; ?>" VALUE="Abstain" <? if ($vote == "Abstain") print "CHECKED"; ?>> <?= $abstain; ?></p>
                            </div>
</div>
<?php } ?>

<div align="center"><input type="submit" value="Vote" name="submit" class="btn btn-wmffl text-align-center"/></div>

        </FORM>

    <?php }
} else {
    ?>

    <CENTER><B>You must be logged in to cast your votes </B></CENTER>

<?php } ?>
</div>

<?php include "base/footer.php"; ?>

