<?php
/**
 * Table Definition for weeklyplayerscores
 */
require_once 'DB/DataObject.php';

class DataObjects_Weeklyplayerscores extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'weeklyplayerscores';              // table name
    public $playerid;                        // int(11)  not_null primary_key auto_increment
    public $name;                            // string(51)  
    public $pos;                             // string(2)  enum
    public $nflteam;                         // string(3)  
    public $teamid;                          // int(11)  multiple_key
    public $teamname;                        // string(50)  unique_key
    public $season;                          // int(11)  not_null primary_key
    public $week;                            // int(11)  not_null primary_key
    public $pts;                             // int(11)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Weeklyplayerscores',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
