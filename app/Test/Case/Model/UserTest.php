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
    public function testFromFilterToQueryConditions_username()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'username', 
                    2 => 'equal-to', 
                    3 => 'john')
                )
            );
        $this->assertEqual(
            $this->User->fromFilterToQueryConditions($filter),
            array("username" => "john"));

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => "username", 
                    2 => "start-with", 
                    3 => "jo")
                )
            );        
        $this->assertEqual(
            $this->User->fromFilterToQueryConditions($filter),
            array("username LIKE" => "jo%"));
    }
    
    
    public function testFromFilterToQueryConditions_group()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'group_id', 
                    2 => 'is', 
                    3 => '1')
                )
            );        
        $this->assertEqual(
            $this->User->fromFilterToQueryConditions($filter),
            array('group_id' => '1')
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'group_id', 
                    2 => 'not-is', 
                    3 => '1')
                )
            );        
        $this->assertEqual(
            $this->User->fromFilterToQueryConditions($filter),
            array('group_id' => array('$ne' => '1'))
            );
    }
    

}
