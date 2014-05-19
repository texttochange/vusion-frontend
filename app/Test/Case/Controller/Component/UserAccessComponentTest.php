<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('UserAccessComponent', 'Controller/Component');
App::uses('ProgramSetting', 'Model');


class TestUserAccessComponentController extends Controller
{
    var $components = array('UserAccess');
}


class UserAccessComponentTest extends CakeTestCase
{
    public $UserAccessComponent = null;
    public $Controller = null;
    public $fixtures = array('app.program','app.group','app.user', 'app.programsUser');
    
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
    
    
    public function dropData()
    {
        $this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->deleteAll(true, false);  
        $this->ProgramSettingM6H = new ProgramSetting(array('database' => 'm6h'));
        $this->ProgramSettingM6H->deleteAll(true, false);
        $this->ProgramSettingTrial = new ProgramSetting(array('database' => 'trial'));
        $this->ProgramSettingTrial->deleteAll(true, false);
    }
    
    
    public function teardown()
    {
        unset($this->UserAccessComponent);
        unset($this->Controller);
        parent::teardown();
    }
    
    
    public function testGetUnmatchableConditions_allProgramAccess()
    {
        $this->AssertEqual(array(), $this->UserAccessComponent->getUnmatchableConditions());
    }
    
    
    public function testGetUnmatchableConditions_hasSpecificProgramAccess()
    {
        $groupMock = $this->getMock('Group', array('hasSpecificProgramAccess'));        
        $groupMock
        ->expects($this->once())
        ->method('hasSpecificProgramAccess')
        ->will($this->returnValue(true));
        
        $authMock = $this->getMock('Auth', array('user'));        
        $authMock
        ->expects($this->any())
        ->method('user')
        ->will($this->returnValue(array(
            'id' => '1',
            'group_id' => '1')));
        
        $this->UserAccessComponent->Group = $groupMock;
        $this->UserAccessComponent->Auth = $authMock;
        
        $this->ProgramSettingTest = new ProgramSetting(array('database' => 'testdbprogram'));
        $this->ProgramSettingTest->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSettingTest->saveProgramSetting('shortcode','256-8282');
        
        $expected = array('$or' => array(
            array('participant-phone' => array('$in' => array(0 => '256-8282'))),
            array('to' => array('$in' => array(0 => '8282'))),
            ));
        
        $this->AssertEqual($expected, $this->UserAccessComponent->getUnmatchableConditions());
    }
    
    
}
