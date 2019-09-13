#!/usr/bin/perl

#BEGIN {
#	push(@lib,("../../Unzip"));
#}

#use lib(@lib);

use TAPORlib 8.70;
use Archive::Zip qw(:ERROR_CODES);
use Archive::Zip::Tree;

$zip = Archive::Zip->new();
$zipName = "./myzip.zip";
#$zipName = "/home/Administrator/c/Program\ Files/FLM2000/Resource/f0204a.fs0";
#$zipName = "../../Unzip/f0204a.fs0";
$status = $zip->read( $zipName );
print $status;
#$status = $zip->extractTree('',"./unzip/");
$status = $zip->extractMember("indstats.nfl", "indstats.nfl");
#$status = $zip->extractMember("indstats.nfl", "./unzip/indstats.nfl");
print $status;
