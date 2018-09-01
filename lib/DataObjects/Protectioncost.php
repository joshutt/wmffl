<?php
/**
 * Table Definition for protectioncost
 */
require_once 'DB/DataObject.php';

class DataObjects_Protectioncost extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'protectioncost';      // table name
    public $playerid;                        // int(11)  not_null primary_key
    public $season;                          // year(4)  not_null primary_key unsigned zerofill
    public $years;                           // int(11)  

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
