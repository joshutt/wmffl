<?

function dateDiff($month1, $day1, $year1, $month2, $day2, $year2) {

    $jd1 = gregoriantojd($month1, $day1, $year1);
    $jd2 = gregoriantojd($month2, $day2, $year2);

    $diff = $jd1 - $jd2;
    if ($diff < 0) {
        $diff *= -1;
    }

    return $diff;
}


function addDays($month1, $day1, $year1, $numDays) {
    $jd = gregoriantojd($month1, $day1, $year1);
    return JDToGregorian($jd+$numDays);
}


function getffDate($number) {
    return addDays(12, 30, 1899, $number);
}


function getffTime($number) {
    $newNum = $number - floor($number);
    $newNum *= 24;
    $hour = floor($newNum);
    $newNum -= $hour;
    $newNum *= 60;
    $minute = round($newNum);
    if ($minute == 60) {
        $hour++;
        $minute = 0;
    }
    #return "$hour:$minute";
    return sprintf("%d:%02.0f", $hour, $minute);
}


?>
