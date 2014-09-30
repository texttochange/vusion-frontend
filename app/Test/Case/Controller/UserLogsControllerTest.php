<?php
App::uses('UserLogsController', 'Controller');
App::uses('Program', 'Model');
App::uses('ScriptMaker', 'Lib');


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
    
    
    var $programData = array(
        0 => array( 
            'Program' => array(
                'name' => 'Test Name',
                'url' => 'testurl',
                'timezone' => 'utc',
                'database' => 'testdbprogram',
                'status' => 'running'
                )
            ));
    

    public function setUp()
    {
        parent::setUp();
        
        $this->UserLogs = new TestUserLogsController();
        $this->UserLog  = new UserLog();
        
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
                    'Acl' => array('check')
                    ),
                'models' => array(
                    'UserLog' => array('getUserLogs'),
                    'Group' => array()
                    ),
                )
            );
        
        $userLogs->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        return $userLogs;
    }
    
    
    public function testIndex()
    {
        $userLog2 = array(
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
        
        $userLogs = $this->mock_program_access();
        //print_r($userLogs);
        $userLogs->UserLog
        ->expects($this->once()) 
        ->method('getUserLogs');
       //->will($this->returnValue($userLog2));
        
        $this->testAction("testurl/userlogs/index");        
    }
}
