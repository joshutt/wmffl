<?
require_once "Date.php";

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
//    mktime(0, 0, 0, 12, 30+$number, 1899);
    $newDate = new Date("1899-12-30");
    $max = pow(2, 31) - 1;
    $num = $number * 60 * 60 * 24;
    while ($num > $max) {
        $newDate->addSeconds($max);
        $num -= $max;
    }
    $newDate->addSeconds($num);
    return $newDate;
#    return addDays(12, 30, 1899, $number);
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


function getDateArray($number) {
    return getdate(strtotime(getffDate($number)));
}


function getMysqlDate($date) {
    $dateString = $date->format("%Y-%m-%d %T");
    return $dateString;
}
?>
