<?
require_once "config.php";

function openZip($dataPath, $zipFile) {
    $zip = zip_open($dataPath.'/'.$zipFile);
    if ($zip) {
        while ($zip_entry = zip_read($zip)) {
            $fileName = zip_entry_name($zip_entry);
            zip_entry_open($zip, $zip_entry);
            $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            $file = fopen($dataPath.'/'.$fileName, 'w');
            fwrite($file, $buf);
            fclose($file);
        }
        zip_close($zip);
    }
}



//$ftp_conn = ftp_connect("www.fflm.com");
$ftp_conn = ftp_connect($ftp_server);

// login with username and password
$login_result = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass); 

// check connection
if ((!$ftp_conn) || (!$login_result)) { 
    echo "FTP connection has failed!";
    echo "Attempted to connect to $ftp_server for user $ftp_user_name"; 
    exit; 
} else {
    echo "Connected to $ftp_server, for user $ftp_user_name";
}


/*
// upload the file
$upload = ftp_put($ftp_conn, $destination_file, $source_file, FTP_BINARY); 

// check upload status
if (!$upload) { 
    echo "FTP upload has failed!";
} else {
    echo "Uploaded $source_file to $ftp_server as $destination_file";
}
*/
/*
$contents = ftp_nlist($ftp_conn, "files/nfl");
print $contents."\n";
foreach ($contents as $entry) {
    print $entry."\n";
}
*/

//ftp_get($ftp_conn, $DATA_LOC."/mast2003.txt", "files/nfl/mast2003.txt", FTP_ASCII);
ftp_get($ftp_conn, $master_loc, $ret_loc.$master_name, FTP_ASCII);

foreach ($zipProcessFiles as $zipFile) {
    $fullZipPath = $DATA_LOC."/".$zipFile;
    ftp_get($ftp_conn, $fullZipPath, $ret_loc.$zipFile, FTP_BINARY);
    openZip($DATA_LOC, $zipFile);
}

// close the FTP stream 
ftp_close($ftp_conn); 

?>
