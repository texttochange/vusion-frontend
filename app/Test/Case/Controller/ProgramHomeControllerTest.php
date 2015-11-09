<?php
App::uses('ProgramHomeController', 'Controller');
App::uses('ScriptMaker', 'Lib');
App::uses('UnattachedMessage','Model');
App::uses('ProgramSetting', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


class TestProgramHomeController extends ProgramHomeController
{

    public $autoRender = false;

    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }

}


class ProgramHomeControllerTestCase extends ControllerTestCase
{

    var $programData = array(
        0 => array( 
            'Program' => array(
                'name' => 'Test Name',
                'url' => 'testurl',
                'database' => 'testdbprogram',
                'status' => 'running'
                )
            ));
    
    
    public function setUp() 
    {
        parent::setUp();
        
        $this->ProgramHome = new TestProgramHomeController();
        
        $dbName = $this->programData[0]['Program']['database'];
        $this->Dialogue = ProgramSpecificMongoModel::init(
            'Dialogue', $dbName, true);
        $this->Schedule = ProgramSpecificMongoModel::init(
            'Schedule', $dbName, true);
        $this->UnattachedMessage = ProgramSpecificMongoModel::init(
            'UnattachedMessage', $dbName, true);
        $this->ProgramSetting = ProgramSpecificMongoModel::init(
            'ProgramSetting', $dbName, true);

        $this->ScriptMaker = new ScriptMaker();
        
    }
    
    
    protected function dropData()
    {
        $this->Dialogue->deleteAll(true, false);
        $this->Schedule->deleteAll(true, false);
        $this->UnattachedMessage->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        unset($this->Scripts);        
        parent::tearDown();
    }
    
    
    protected function mockProgramAccess()
    {
        $home = $this->generate('ProgramHome', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read'),
                'LocalizeUtils' => array('localizeLabelInArray'),
                'Auth' => array('loggedIn')
                ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
                ),
            'methods' => array(
                '_instanciateVumiRabbitMQ'),
            ));
        
        $home->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));

        $home->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue('true'));
               

        $home->Program
        ->expects($this->once())
        ->method('find')
        ->will($this->returnValue($this->programData));
        
        $home->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->returnValue($this->programData[0]['Program']['database']));
        
        return $home;
    }
    
        
    public function testIndex()
    {
        /*
        $this->mockProgramAccess();
        $this->testAction("/testurl/home", array('method' => 'get'));
        $this->assertEquals($this->vars['programDetails']['name'], $this->programData[0]['Program']['name']);
        $this->assertEquals($this->vars['programDetails']['url'], $this->programData[0]['Program']['url']);
        */
    }

    
}
