<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('ArchivedProgramComponent', 'Controller/Component');


class TestArchivedProgramComponentController extends Controller
{
	function redirect($url) {
		return;
	}

    function render($view) {
        return;
    }
}


class ArchivedProgramComponentTest extends CakeTestCase
{
	public $ArchiveComponent = null;
    public $Controller       = null;
    
    
    public function setUp() 
    {
        parent::setUp();
        $Collection             = new ComponentCollection();
        $this->ArchiveComponent = new ArchivedProgramComponent($Collection);
    }   
    
    
    public function tearDown()
    { 
        unset($this->ArchiveComponent);
        parent::tearDown();
    }

    
    private function _initializeRequest($controllerName, $method='POST', $action='add', $isAjax=false) {

    	$CakeRequest = $this->getMock('CakeRequest',
            array('__get', 'method', 'is'));

    	$CakeRequest->action = $action;
    	$CakeRequest->params = array(
            'controller' => $controllerName
            );

    	$CakeRequest
            ->expects($this->once())
            ->method('method')
            ->will($this->returnValue($method));

        $CakeRequest
            ->expects($this->once())
            ->method('is')
            ->will($this->returnValue($isAjax));

        $CakeResponse     = new CakeResponse();
        $this->Controller = new TestArchivedProgramComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
      
        $this->Controller->params['program'] = 'something';
        
		$this->Controller->programDetails = array('status' => 'archived');
		$this->ArchiveComponent->initialize($this->Controller);
    }

    
    public function testIsAllowed_notAllowed() 
    {
    	$this->_initializeRequest("someController");
    	$this->ArchiveComponent->Session = $this->getMock('Session', array('setFlash'));
		$this->ArchiveComponent->Session 
			->expects($this->once())
			->method('setFlash')
			->with('Adding this is not allowed within an archived program.');
		$this->assertFalse($this->ArchiveComponent->isAllowed($this->Controller));

        $this->_initializeRequest("programHome", 'GET', 'restartWorker');
        $this->ArchiveComponent->Session = $this->getMock('Session', array('setFlash'));
        $this->ArchiveComponent->Session 
            ->expects($this->once())
            ->method('setFlash')
            ->with('Restart worker is not allowed in archived program.');
        $this->assertFalse($this->ArchiveComponent->isAllowed($this->Controller));

        $this->_initializeRequest("programHome", 'GET', 'restartWorker', true);
        $this->ArchiveComponent->Session = $this->getMock('Session', array('setFlash'));
        $this->ArchiveComponent->Session 
            ->expects($this->once())
            ->method('setFlash')
            ->with('Restart worker is not allowed in archived program.');
        $this->assertFalse($this->ArchiveComponent->isAllowed($this->Controller));
	}


}
