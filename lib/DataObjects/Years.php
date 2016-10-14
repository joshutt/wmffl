<?php
/**
 * Table Definition for years
 */
require_once 'DB/DataObject.php';

class DataObjects_Years extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'years';                           // table name
    public $season;                          // year(4)  not_null primary_key unsigned zerofill

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Years',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
