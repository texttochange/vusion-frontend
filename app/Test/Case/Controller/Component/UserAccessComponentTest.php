<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('UserAccessComponent', 'Controller/Component');


class TestUserAccessComponentController extends Controller
{
    var $components = array('UserAccess');
}


class UserAccessComponentTest extends CakeTestCase
{
    public $UserAccessComponent = null;
    public $Controller = null;
    //public $fixtures = array('app.program','app.group','app.user', 'app.programsUser');
    
    public function setup()
    {
        parent::setup();
        $Collection = new ComponentCollection();
        $this->UserAccessComponent = new UserAccessComponent($Collection);
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        
        $this->Controller = new TestUserAccessComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
        $this->UserAccessComponent->initialize($this->Controller);
        //$this->UserAccessComponent->startup($this->Controller);
    }
    
    
    public function teardown()
    {
        unset($this->UserAccessComponent);
        unset($this->Controller);
        parent::teardown();
    }
    
    /*
    public function testGetUnmatchableConditions_NoUnmatchableAccess()
    {
        $this->AssertEqual(array(), $this->UserAccessComponent->getUnmatchableConditions());
    }
    */
    
    public function testGetUnmatchableConditions_hasUnmatchableAccess_hasSpecificProgramAccess()
    {
        $groupMock = $this->getMock('Group', array('hasSpecificProgramAccess'));
        
        $groupMock
        ->expects($this->once())
        ->method('hasSpecificProgramAccess')
        //->with('id'==1)
        ->will($this->returnValue(true));
        
        $this->UserAccessComponent->Group = $groupMock;
        print_r($this->UserAccessComponent->Program->find());
        
        $this->AssertEqual(array(), $this->UserAccessComponent->getUnmatchableConditions());
    }
    
    /*
    public function testGetUnmatchableConditions_hasUnmatchableAccess_allProgramsAccess()
    {
        $this->AssertEqual(array(), $this->UserAccessComponent->getUnmatchableConditions());
    }
    */
    
}
