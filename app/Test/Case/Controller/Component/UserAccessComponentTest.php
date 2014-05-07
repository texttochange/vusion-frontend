<?php
App::uses('Controller', 'Controller');
App::uses('ComponentCollection', 'Controller');
App::uses('UserAccessComponent', 'Controller/Component');


class TestUserAccessComponentController extends Controller
{
}


class UserAccessComponentTest extends CakeTestCase
{
    public $fixtures = array('app.program','app.group','app.user', 'app.programsUser');
    
    public function setup()
    {
        parent::setup();
        $Collection = new ComponentCollection();
        $this->UserAccessComponent = new UserAccessComponent($Collection);
    }
    
    
    public function teardown()
    {
        parent::teardown();
        unset($this->UserAccessComponent);
    }
    
    public function testGetUnmatchableConditions_NoUnmatchableAccess()
    {
        $this->AssertEqual(array(), $this->UserAccessComponent->getUnmatchableConditions());
    }
    
    
    public function testGetUnmatchableConditions_hasUnmatchableAccess_hasSpecificProgramAccess()
    {
        $User = $this->getMock('Group', array('hasSpecificProgramAccess'));
        
        $User
        ->expects($this->once())
        ->method('hasSpecificProgramAccess')
        ->will($this->returnValue(true));
        
        $this->AssertEqual(array(), $this->UserAccessComponent->getUnmatchableConditions());
    }
    
    
    public function testGetUnmatchableConditions_hasUnmatchableAccess_allProgramsAccess()
    {
        $this->AssertEqual(array(), $this->UserAccessComponent->getUnmatchableConditions());
    }
    
    
}
