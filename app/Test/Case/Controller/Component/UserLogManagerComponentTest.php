<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('UserLogManagerComponent', 'Controller/Component');


class TestUserLogManagerComponentController extends Controller
{
	function redirect($url) {
		return;
	}

    function render($view) {
        return;
    }
}


class UserLogManagerComponentComponentTest extends CakeTestCase
{
	public $UserLogComponent = null;
    public $Controller       = null;
    
    
    public function setUp() 
    {
        parent::setUp();
        $Collection             = new ComponentCollection();
        $this->UserLogComponent = new UserLogManagerComponent($Collection);
    }   
    
    
    public function tearDown()
    { 
        unset($this->UserLogComponent);
        parent::tearDown();
    }

    
    private function _initializeRequest($controllerName, $method='POST', $action='add', $isAjax=false) 
    {
    	$CakeRequest           = $this->getMock('CakeRequest',
            array('__get', 'method', 'is'));

    	$CakeRequest->action = $action;
    	$CakeRequest->params = array(
            'controller' => $controllerName
            );

    	$CakeRequest
            ->expects($this->once())
            ->method('method')
            ->will($this->returnValue($method));
            
        $CakeResponse     = new CakeResponse();
        $this->Controller = new TestUserLogManagerComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
      
        $this->Controller->params['program'] = 'something';
        
		$this->Controller->programDetails = array('name' => 'm9rh', 'database' => 'm9rhDB');
		$this->UserLogComponent->initialize($this->Controller);
    }

    
    public function testLogAction() 
    { 
    	$this->_initializeRequest("someController");
    	
    	$this->UserLogComponent->Session = $this->getMock('Session', array('setFlash', 'read'));
    	
    	$this->UserLogComponent->Session 
            ->expects($this->at(0))
            ->method('read')
            ->with('Auth.User.id')
            ->will($this->returnValue(15));
            
        $this->UserLogComponent->Session 
            ->expects($this->at(1))
            ->method('read')
            ->with('Auth.User.username')
            ->will($this->returnValue('Tomx'));
            
		$this->UserLogComponent->Session 
			->expects($this->once())
			->method('setFlash')
			->with('default');
		$this->assertTrue($this->UserLogComponent->logAction());
	}
	
	


}
