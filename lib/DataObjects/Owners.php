<?php
/**
 * Table Definition for owners
 */
require_once 'DB/DataObject.php';

class DataObjects_Owners extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'owners';                          // table name
    public $teamid;                          // int(11)  not_null primary_key
    public $userid;                          // int(11)  not_null primary_key
    public $season;                          // year(4)  not_null primary_key unsigned zerofill
    public $primary;                         // int(4)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Owners',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
