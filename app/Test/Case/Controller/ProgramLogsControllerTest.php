<?php

App::uses('ProgramLogsController', 'Controller');


class TestProgramLogsController extends ProgramLogsController
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
    
}


class ProgramLogsControllerTestCase extends ControllerTestCase
{
     
    var $programData = array(
        0 => array( 
            'Program' => array(
                'name' => 'Test Name',
                'url' => 'testurl',
                'timezone' => 'utc',
                'database' => 'testdbprogram'
                )
        ));
    
    var $programLogs = array(
        '[[2012-04-23T12:00:12] Starting send_scheduled()]' => 1335171612,
        '[[2012-04-23T12:01:12] Starting daemon_process()]' => 1335171672,
        '[[2012-04-23T12:01:12] Starting send_scheduled()]' => 1335171672
        );
    
        
    public function setUp()
    {
        parent::setUp();
        
        $this->Logs = new TestProgramLogsController();
        
        $this->dropData();
    }
    
   // we only mock the data to be used so we dont need a dropData() function;    
    
    public function tearDown()
    {
        unset($this->Logs);
        
        parent::tearDown();
    }
    
    
    public function mock_program_access()
    {
        $logs = $this->generate(
            'ProgramLogs', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read')
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                'methods' => array(
                    '_getRedisZRange'
                    )
                )
        );
        
        $logs->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        
        $logs->Program
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->programData));
            
        $logs->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls(
                '4', 
                '2',
                $this->programData[0]['Program']['database'],
                $this->programData[0]['Program']['name'],
                'utc',
                'testdbprogram'
            ));
     
        return $logs;
    }
    
    /**
    * Test Methods
    *
    */
    
    public function testIndex()
    {
        $logs = $this->mock_program_access();
        $logs
            ->expects($this->once())
            ->method('_getRedisZRange')
            ->will($this->returnValue($this->programLogs));
        
        $this->testAction("testurl/programLogs/index");
             
    }
    
    
    public function testGetBackendNotifications()
    {
        $programLogs = array(
             '[[2012-04-23T12:00:12] Starting send_scheduled()]' => 1335171612,
             '[[2012-04-23T12:01:12] Starting daemon_process() 1]' => 1335171672,
             '[[2012-04-23T12:01:12] Starting daemon_process() 2]' => 1335171672,
             '[[2012-04-23T12:01:12] Starting daemon_process() 3]' => 1335171672,
             '[[2012-04-23T12:01:12] Starting send_scheduled()]' => 1335171672
        );
             
        $logs = $this->mock_program_access();
        $logs
            ->expects($this->once())
            ->method('_getRedisZRange')
            ->will($this->returnValue($programLogs));           
                
        $this->testAction("testurl/programLogs/getBackendNotifications");
        $this->assertEquals(5,count($this->vars['programLogs']));
    }
    
    
}

