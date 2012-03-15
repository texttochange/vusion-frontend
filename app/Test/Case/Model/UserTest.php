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
    

}
