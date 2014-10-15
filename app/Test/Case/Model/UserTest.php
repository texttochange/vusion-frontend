<?php
/* User Test cases generated on: 2012-01-24 15:34:07 : 1327408447*/
App::uses('User', 'Model');

/**
* User Test Case
*
*/
class UserTestCase extends CakeTestCase
{
    /**
    * Fixtures
    *
    * @var array
    */
    public $fixtures = array('app.user', 'app.group', 'app.program', 'app.programs_user');
    
    /**
    * setUp method
    *
    * @return void
    */
    public function setUp()
    {
        parent::setUp();
        
        $this->User = ClassRegistry::init('User');
    }
    
    /**
    * tearDown method
    *
    * @return void
    */
    public function tearDown()
    {
        unset($this->User);
        
        parent::tearDown();
    }
    
    
    public function testNothing()
    {
        $this->assertTrue(true);
    }
    
    //TEST FILTERS
    public function testfromFilterToQueryCondition_username()
    {
        $filterParam = array(
            1 => 'username', 
            2 => 'equal-to', 
            3 => 'john');
        $this->assertEqual(
            $this->User->fromFilterToQueryCondition($filterParam),
            array("username" => "john"));
        
        $filterParam = array(
                    1 => "username", 
                    2 => "start-with", 
                    3 => "jo");        
        $this->assertEqual(
            $this->User->fromFilterToQueryCondition($filterParam),
            array("username LIKE" => "jo%"));
    }
    
    
    public function testfromFilterToQueryCondition_group()
    {
        $filterParam = array(
            1 => 'group_id', 
            2 => 'is', 
            3 => '1');        
        $this->assertEqual(
            $this->User->fromFilterToQueryCondition($filterParam),
            array('group_id' => '1'));
        
        $filterParam = array(
            1 => 'group_id', 
            2 => 'not-is', 
            3 => '1');
        $this->assertEqual(
            $this->User->fromFilterToQueryCondition($filterParam),
            array('group_id' => array('$ne' => '1')));
    }
    
    
}
