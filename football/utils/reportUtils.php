<?php

$supportedFormats = array("html", "csv", "json", "ajax");

function supportedFormat($format) {
    global $supportedFormats;
    return in_array($format, $supportedFormats);
}


/**
 * Given an array of titles and the content print out in a table
 * @param $titles List of the headers for the columns
 * @param $content The data to fill in
 */
function outputHtml($titles, $content) {

    print "<table id=\"statTable\"> <thead> <tr>";
    foreach ($titles as $subj) {
        print "<th>$subj</th>";
    }
    print "</tr> </thead> <tbody>";
    foreach ($content as $player) {
        print "<tr>";
        foreach ($player as $item) {
	    print "<td>$item</td>";
        }   
    print "</tr>";
    } 

    print "</thbody></table>";
}


function outputCSV($titles, $content, $filename="data.csv") {
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

function outputJSON($titles, $content) {
    $jsonArr = array();
    foreach ($content as $item) {
	$jsonObj = array();
        for ($i = 0; $i < sizeof($titles); $i++) {
            $jsonObj[$titles[$i]] = $item[$i];
        }
        array_push($jsonArr, $jsonObj);
    }   

    $json_output = json_encode($jsonArr);
    print $json_output;
}
