<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('ProgramAuthComponent', 'Controller/Component');



class TestProgramAuthComponentController extends Controller
{
    var $uses = array(
        'Program',
        'Group');
    var $components = array(
        'Stats',
        'LogManager',
        'CreditManager',
        'Session');
    var $programDetails = null;

    function _isAjax() {
        return false;
    }

    function _initialize() {
        return;
    }

    function redirect($url) {
        return;
    }

    function render($view) {
        return;
    }
}

class ProgramAuthComponentTest extends CakeTestCase
{

    public $fixtures = array(
        'app.program',
        'app.user',
        'app.programsUser',
        'app.group');

    
    public function setUp() 
    {
        parent::setUp();
        $Collection                 = new ComponentCollection();
        $this->ProgramAuthComponent = new ProgramAuthComponent($Collection);
    }
    
    
    public function tearDown()
    { 
        unset($this->ProgramAuthComponent);
        parent::tearDown();
    }

    private function _initializeRequest($programUrl, $controllerName="someController",
        $method='POST', $action='add', $isAjax=false) {

        $CakeRequest = $this->getMock('CakeRequest',
            array('__get', 'method', 'is'));

        $CakeRequest->action = $action;
        $CakeRequest->params = array(
            'controller' => $controllerName
            );

        $CakeResponse     = new CakeResponse();
        $this->Controller = new TestProgramAuthComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
      
        $this->Controller->params['program'] = $programUrl;
        
        $this->ProgramAuthComponent->initialize($this->Controller);
    }

    /**
     * @expectedException NotFoundException
     */
    public function testStartup_programUrl_notDefined()
    {
        $this->_initializeRequest("");
        $this->ProgramAuthComponent->Auth = $this->getMock('Auth', array('loggedIn', 'user'));
        $this->ProgramAuthComponent->Auth
            ->expects($this->once())
            ->method('loggedIn')
            ->will($this->returnValue(true));

        $this->ProgramAuthComponent->startup($this->Controller);
    }

    /**
     * @expectedException ForbiddenException
     */
    public function testStartup_user_notLoggedIn()
    {
        $this->_initializeRequest("");
        $this->ProgramAuthComponent->Auth = $this->getMock('Auth', array('loggedIn', 'user'));
        $this->ProgramAuthComponent->Auth
            ->expects($this->once())
            ->method('loggedIn')
            ->will($this->returnValue(false));

        $this->ProgramAuthComponent->startup($this->Controller);
    }

    /**
     * @expectedException NotFoundException
     */
    public function testStartup_user_notAccess_program()
    {
        $this->_initializeRequest("test");
        $this->ProgramAuthComponent->Auth = $this->getMock('Auth', array('loggedIn', 'user'));
        $this->ProgramAuthComponent->Auth
            ->expects($this->once())
            ->method('loggedIn')
            ->will($this->returnValue(true));

        $this->ProgramAuthComponent->Auth
            ->expects($this->at(1))
            ->method('user')
            ->with('group_id')
            ->will($this->returnValue(2));

           $this->ProgramAuthComponent->Auth
            ->expects($this->at(2))
            ->method('user')
            ->with('id')
            ->will($this->returnValue(2));

        $this->ProgramAuthComponent->startup($this->Controller);
    }

    public function testStartup_ok()
    {
        $this->_initializeRequest("m6h");
        $this->ProgramAuthComponent->Auth = $this->getMock('Auth', array('loggedIn', 'user'));
        $this->ProgramAuthComponent->Auth
            ->expects($this->once())
            ->method('loggedIn')
            ->will($this->returnValue(true));

        $this->ProgramAuthComponent->Auth
            ->expects($this->at(1))
            ->method('user')
            ->with('group_id')
            ->will($this->returnValue(2));

           $this->ProgramAuthComponent->Auth
            ->expects($this->at(2))
            ->method('user')
            ->with('id')
            ->will($this->returnValue(2));

        $this->Controller->LogManager = $this->getMock('LogManager', array('getLogs'));
        $this->Controller->LogManager
            ->expects($this->once())
            ->method('getLogs')
            ->with('m6h')
            ->will($this->returnValue(array()));

        $this->Controller->Stats = $this->getMock('Stats', array('getProgramStats'));
        $this->Controller->Stats
            ->expects($this->once())
            ->method('getProgramStats')
            ->with('m6h', true)
            ->will($this->returnValue(array()));

        $this->Controller->CreditManager = $this->getMock('CreditManager', array('getOverview'));
        $this->Controller->CreditManager
            ->expects($this->once())
            ->method('getOverview')
            ->with('m6h')
            ->will($this->returnValue(array()));

        $this->ProgramAuthComponent->startup($this->Controller);

        $this->assertEqual(
            array(
                'name' => 'm6h',
                'url' => 'm6h',
                'database' => 'm6h',
                'status' => 'running',
                'settings' => array()), 
            $this->Controller->programDetails);
    }


}

