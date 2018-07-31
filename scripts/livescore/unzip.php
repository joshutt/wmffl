<?

$inputFile = "data/myzip.zip";
$outputFile = "data/indstats.nfl";
$outputDir = "data";
if (count($argv) >= 2) {
    $inputFile = $argv[1];
    if (count($argv) >= 3) {
        $outputFile = $argv[2];
    }
}

system("`which unzip` -u $inputFile -d $outputDir");

// //$zip = zip_open("./myzip.zip");
// $zip = zip_open($inputFile);

// while ($zip_entry = zip_read($zip)) {
//    if (zip_entry_name($zip_entry) == "indstats.nfl") {
//        zip_entry_open($zip, $zip_entry, "r");
//        $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
//        zip_entry_close($zip_entry);

//        //$handle = fopen("indstats.nfl", "w");
//        $handle = fopen($outputFile, "w");
//        fwrite($handle, $buf);
//        fclose($handle);
//        break;
//    }
//}
//zip_close($zip);


?>
