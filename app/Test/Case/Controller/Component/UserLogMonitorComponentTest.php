<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('UserLogMonitorComponent', 'Controller/Component');
App::uses('UserLog', 'Model');


class TestUserLogMonitorComponentController extends Controller
{
    function redirect($url) {
        return;
    }
    
    function render($view) {
        return;
    }
}


class UserLogMonitorComponentTest extends CakeTestCase
{
    public $UserLogComponent = null;
    public $Controller       = null;
    
    public function setUp() 
    {  
        parent::setUp();
        $Collection             = new ComponentCollection();
        $this->UserLogComponent = new UserLogMonitorComponent($Collection);
        
        $CakeRequest = $this->getMock('CakeRequest',
            array('__get', 'method', 'is'));
        
        $CakeRequest->action = 'add';
        $CakeRequest->params = array(
            'controller' => 'pRograMs'
            );
        
        $CakeRequest
        ->expects($this->once())
        ->method('method')
        ->will($this->returnValue('POST'));
        
        $this->UserLog    = ClassRegistry::init('UserLog');
        $CakeResponse     = new CakeResponse();
        $this->Controller = new TestUserLogMonitorComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
        
        $this->Controller->programDetails = array(
            'name' => 'm9rh',
            'database' => 'm9rhDB',
            'settings' => array('timezone' => 'Africa/Kampala'));
        
        $this->UserLogComponent->initialize($this->Controller);
    }
    
    
    protected function dropData()
    {
        $this->UserLog->deleteAll(true, false);
    }
    
    
    public function tearDown()
    { 
        $this->dropData();
        unset($this->UserLogComponent);
        parent::tearDown();
    }
    
    
    public function testLogAction() 
    { 
        $this->UserLogComponent->Auth = $this->getMock('Auth', array(
            'user'));
        
        $this->UserLogComponent->Auth 
        ->expects($this->at(0))
        ->method('user')
        ->with('id')
        ->will($this->returnValue(89));
        
        $this->UserLogComponent->Auth 
        ->expects($this->at(1))
        ->method('user')
        ->with('username')
        ->will($this->returnValue('Tomx'));
        
        $this->UserLogComponent->setEventData('m9rh');
        
        $this->UserLogComponent->logAction();
        
        $saveUserLog = $this->UserLog->find('all');
        
        $this->assertEqual('m9rhDB',
            $saveUserLog[0]['UserLog']['program-database-name']);
        
        $this->assertEqual('Added a new program m9rh',
            $saveUserLog[0]['UserLog']['parameters']);
    }
    
    
    public function testLogAction_no_eventData() 
    { 
        $this->UserLogComponent->Auth = $this->getMock('Auth', array(
            'user'));
        
        $this->UserLogComponent->Auth 
        ->expects($this->at(0))
        ->method('user')
        ->with('id')
        ->will($this->returnValue(89));
        
        $this->UserLogComponent->Auth 
        ->expects($this->at(1))
        ->method('user')
        ->with('username')
        ->will($this->returnValue('Tomx'));
        
        
        $this->UserLogComponent->logAction();
        
        $saveUserLog = $this->UserLog->find('all');
        
        $this->assertEqual('Added a new program',
            $saveUserLog[0]['UserLog']['parameters']);
    }
    
    
}
