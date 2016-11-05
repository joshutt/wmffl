<?php
/**
 * Table Definition for expansionLost
 */
require_once 'DB/DataObject.php';

class DataObjects_ExpansionLost extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'expansionLost';                   // table name
    public $teamid;                          // int(11)  not_null primary_key
    public $num;                             // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_ExpansionLost',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
