<?
# Path Info
$MYROOT = "/home/wmffl/public_html";
$LIB = $MYROOT."/lib/";
$DATA_LOC = $MYROOT."/devel/updateplayers/data";

# Database info
$database = 'wmffl_test';
$db_host = 'localhost';
$db_user = 'wmffl_other';
$db_pass = 'other';

# FTP information
$ftp_server = "www.fflm.com";
$ftp_user_name = "anonymous";
$ftp_user_pass = "josh@wmffl.com";
$ret_loc = "files/nfl/";

# base file names
$player_files = "play2003";
$player_delta = "pla!2003";
$tran_files = "tran2003";
$tran_delta = "tra!2003";
$master = "mast2003";

# file extentions
$data_ext = ".nfl";
$zip_ext = ".f-0";
$txt_ext = ".txt";

# Data files
$base_players = $DATA_LOC."/".$player_files.$data_ext;
$delta_players = $DATA_LOC."/".$player_delta.$data_ext;
$base_transactions = $DATA_LOC."/".$tran_files.$data_ext;
$delta_transaction = $DATA_LOC."/".$tran_delta.$data_ext;
$master_name = $master.$txt_ext;
$master_loc = $DATA_LOC."/".$master_name;

# Zipped files
$zipProcessFiles = array ($player_files.$zip_ext, $player_delta.$zip_ext, $tran_files.$zip_ext, $tran_delta.$zip_ext);
?>
