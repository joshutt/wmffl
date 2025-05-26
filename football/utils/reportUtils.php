<?php

function supportedFormat($format): bool
{
    $supportedFormats = array('html', 'csv', 'json', 'ajax');
    return in_array($format, $supportedFormats);
}


/**
 * Given an array of titles and the content print out in a table
 * @param $titles array List of the headers for the columns
 * @param array $content The data to fill in
 */
function outputHtml(array $titles, array $content): void
{

    print "<table id=\"statTable\"> <thead> <tr>";
    foreach ($titles as $subj) {
        print "<th>$subj</th>";
    }
    print '</tr> </thead> <tbody>';
    foreach ($content as $player) {
        print '<tr>';
        foreach ($player as $item) {
            print "<td>$item</td>";
        }
        print '</tr>';
    }

    print '</thbody></table>';
}


function outputCSV($titles, $content, $filename = 'data.csv')
{
    // output headers so that the file is downloaded rather than d`isplayed
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=$filename");

    // create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // output the column headings
    fputcsv($output, $titles);
    foreach ($content as $row) {
        fputcsv($output, $row);
    }
}

function outputJSON($titles, $content)
{
    $jsonArr = array();
    foreach ($content as $item) {
        $jsonObj = array();
        $keys = array_keys($item);
        for ($i = 0; $i < sizeof($titles); $i++) {
            $key = $keys[$i];
            $jsonObj[$titles[$i]] = $item[$key];
        }
        array_push($jsonArr, $jsonObj);
    }

    $json_output = json_encode($jsonArr);
    header('Content-Type: application/json; charset=UTF-8');
    print $json_output;
}
