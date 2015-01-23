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
            'controller' => 'programs'
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
        $this->UserLogComponent->Session = $this->getMock('Session', array(
            'read',
            'check',
            'write',
            'delete'));
        $this->UserLogComponent->Auth = $this->getMock('Auth', array(
            'user'));
        
        $this->UserLogComponent->Session 
        ->expects($this->at(0))
        ->method('check')
        ->with('UserLogMonitor')
        ->will($this->returnValue(true));
        
        $this->UserLogComponent->Session 
        ->expects($this->at(1))
        ->method('read')
        ->with('UserLogMonitor')
        ->will($this->returnValue(array(
            'programDatabaseName' => null,
            'programName' => null)));
        
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
        
        $this->UserLogComponent->Session 
        ->expects($this->at(2))
        ->method('delete')
        ->with('UserLogMonitor');
        
        $this->UserLogComponent->setEventData('m9rh');
        
        $this->UserLogComponent->logAction();
        
        $saveUserLog = $this->UserLog->find('all');
        $this->assertEqual('Added a new program m9rh',
            $saveUserLog[0]['UserLog']['parameters']);
    }
    
}
