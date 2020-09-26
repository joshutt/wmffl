<?php

function shortenInjury($injury)
{
    switch ($injury) {
        case 'P':
        case 'Probable':
            $inj = "Prob";
            break;
        case 'Q':
        case 'Questionable':
            $inj = "Ques";
            break;
        case 'D':
        case 'Doubtful':
            $inj = "Doub";
            break;
        case 'O':
        case 'Out':
            $inj = "Out";
            break;
        case 'I':
        case 'IR':
        case 'IR-NFI':
        case 'IR-PUP':
            $inj = "NFL IR";
            break;
        case 'S':
        case 'Suspended':
            $inj = "Susp";
            break;
        case 'Covid':
        case 'COVID-IR':
        case 'Holdout':
            $inj = "Covid";
            break;
        default:
            $inj = "";
    }
    return $inj;
}


/**
 * @param $injStatus
 * @param $injDetail
 * @param int $ir
 * @return string
 */
function getPQDOLine($injStatus, $injDetail, $ir = 0): string
{
    $injury_detail = "";
    $status = shortenInjury($injStatus);
    if ($status !== "") {
        $injury_detail = $injStatus . ": " . $injDetail;
    }

    if ($ir === "1") {
        $status = "IR";
    }

    $returnLine = "<span class=\"PQDO\" title=\"$injury_detail\">($status)</span>";
    if ($status === "") {
        $returnLine = "";
    }

    return $returnLine;
}

function getIRStatus(): array
{
    return ['IR', 'IR-PUP', 'IR-NFI'];
}

function getIRStatusSql(): string
{
    $returnString = "(";
    $first = TRUE;
    foreach (getIRStatus() as $status) {
        if (!$first) {
            $returnString .= ", ";
        }
        $returnString .= "'$status'";
        $first = FALSE;
    }
    $returnString .= ")";
    return $returnString;
}
