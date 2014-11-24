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
                'database' => 'testdbprogram',
                'status' => 'running'
                )
            ));
    
    
    public function setUp()
    {
        parent::setUp();        
        $this->Logs = new TestProgramLogsController();
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
                    'Session' => array('read'),
                    'LogManager' => array('getLogs'),
                    'Auth' => array('loggedIn'),
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                )
            );
        
        $logs->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $logs->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue('true'));

        $logs->Program
        ->expects($this->once())
        ->method('find')
        ->will($this->returnValue($this->programData));
        
        $logs->Session
        ->expects($this->any())
        ->method('read')
        ->will(
            $this->returnValue($this->programData[0]['Program']['database'])
            );
        
        return $logs;
    }
    
    
    public function testIndex()
    {
        $programLogs = array(
            '[[2012-04-23T12:00:12] Starting send_scheduled()]' => 1335171612,
            '[[2012-04-23T12:01:12] Starting daemon_process()]' => 1335171672,
            '[[2012-04-23T12:01:12] Starting send_scheduled()]' => 1335171672
            );
        
        $logs = $this->mock_program_access();
        $logs->LogManager
        ->expects($this->exactly(2)) //one for the notification window and once for the full page
        ->method('getLogs')
        ->will($this->returnValue($programLogs));
        
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
        $logs->LogManager
        ->expects($this->once())
        ->method('getLogs')
        ->with('testdbprogram', 5)
        ->will($this->returnValue($programLogs));           
        
        $this->testAction("testurl/programLogs/getBackendNotifications.json");
        $this->assertEquals(5, count($this->vars['programLogs']));
    }
    
    
}

