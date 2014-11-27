<?php
App::uses('UserLogsController', 'Controller');


class TestUserLogsController extends UserLogsController
{
    public $autoRender = false;
    
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
}


class UserLogsControllerTestCase extends ControllerTestCase
{
    
   // var $databaseName = "testdbmongo";
    
    public function setUp()
    {        
        parent::setUp();
        
        $this->UserLogs = new TestUserLogsController();
        //$options        = array('database' => $this->databaseName);
        //$this->UserLog  = new UserLog($options);
        $this->UserLog = ClassRegistry::init('UserLog');
        $this->dropData();
    }

    
    protected function dropData()
    {
        $this->UserLog->deleteAll(true, false);
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        unset($this->UserLogs);        
        parent::tearDown();
    }
    
    
    public function mock_program_access()
    {
        $userLogs = $this->generate(
            'UserLogs', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth' => array('loggedIn')
                    ),
                'models' => array(
                    'Group' => array()
                    )
                )
            );
        
        $userLogs->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $userLogs->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue('true'));
        
        return $userLogs;
    }
    
    
    public function testIndex()
    {
        $this->mock_program_access();   
        $userLog = array(
            'timestamp' => '2014-20-10T20:25:00',
            'timezone' => 'Australia/Sydney',
            'user-name' => 'Tom@gmail.com',
            'user-id' => '1',
            'program-name' => 'm6rh program',
            'program-database-name' => 'm6rhP',
            'controller' => 'programParticipant',
            'action' => 'delete',
            'parameters' => 'all participant with tag: today'
            );
        $this->UserLog->create();
        $this->UserLog->save($userLog);     
        
        $this->testAction("/userLogs/index");
        //print_r($this->vars['userLogs']);
        $this->assertEquals(1, count($this->vars['userLogs']));
    }
}
