<?php
/**
 * Table Definition for expandAvailable
 */
require_once 'DB/DataObject.php';

class DataObjects_ExpandAvailable extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'expandAvailable';                 // table name
    public $playerid;                        // int(11)  not_null
    public $teamid;                          // string(50)  
    public $firstname;                       // string(25)  
    public $lastname;                        // string(25)  not_null
    public $pos;                             // string(2)  enum
    public $type;                            // string(9)  enum
    public $cost;                            // int(11)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_ExpandAvailable',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
