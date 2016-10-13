<?php

Class SavePlayersTest extends PHPUnit_Framework_TestCase {
    
    public function testCheckForStatId() {
        $return = checkForStatId(6517);
        $this->assertTrue($return);
        
        $return = checkForStatId(99999);
        $this->assertFalse($return);
    }
    
}