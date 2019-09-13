<?
require_once "config.php";
require_once "pclzip.lib.php";

$httpfile = file_get_contents("http://$ftp_server/$ret_loc$master_name");
$handle=fopen($master_loc, 'w+');
fwrite($handle, $httpfile);
fclose($handle);

foreach ($zipProcessFiles as $zipFile) {
    $fullZipPath = $DATA_LOC."/".$zipFile;
    $httpfile = file_get_contents("http://$ftp_server/$ret_loc$zipFile");
    $handle=fopen($fullZipPath, "w+b");
    fwrite($handle, $httpfile);
    fclose($handle);

    $archive = new PclZip($fullZipPath);
    $archive->extract(PCLZIP_OPT_PATH, $DATA_LOC);
}
#print $httpfile;
?>
