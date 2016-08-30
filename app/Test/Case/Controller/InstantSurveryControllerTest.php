<?php
App::uses('InstantSurveryController', 'Controller');
App::uses('ScriptMaker', 'Lib');
App::uses('TestHelper', 'Lib');
App::uses('Dialogue', 'Model');
App::uses('Program', 'Model');


class TestInstantSurveryController extends InstantSurveryController
{
    
    public $autoRender = false;
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
    protected function _instanciateVumiRabbitMQ() 
    {}
    
}


class InstantSurveryControllerTestCase extends ControllerTestCase
{
    
    public $fixtures = array('app.program','app.group','app.user', 'app.programsUser');
    
    public function setUp()
    {
        parent::setUp();
        $this->InstantSurvery = new TestInstantSurveryController();
        $this->Program = ClassRegistry::init('Program');
        
        $this->ProgramSettingTest = ProgramSpecificMongoModel::init('ProgramSetting', 'testdbprogram', true);
        $this->ProgramSettingM6H = ProgramSpecificMongoModel::init('ProgramSetting', 'm6h', true);
        $this->ProgramSettingTrial = ProgramSpecificMongoModel::init('ProgramSetting', 'trial', true);
        
        $this->maker = new ScriptMaker();
    }
    
    
    protected function dropData()
    { 
        $this->Program->deleteAll(true, false);         
        $this->ProgramSettingTest->deleteAll(true, false);  
        $this->ProgramSettingM6H->deleteAll(true, false);
        $this->ProgramSettingTrial->deleteAll(true, false);
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        unset($this->InstantSurvery);
        parent::tearDown();
    }
    
    
    protected function mockProgramAccess()
    {
        $InstantSurvery = $this->generate(
            'InstantSurvery', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Auth' => array('user', 'loggedIn'),
                    'Session' => array('read'),
                    'Stats',
                    ),
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    )
                )
            );
        
        $InstantSurvery->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $InstantSurvery->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue('true'));
        
        $InstantSurvery->Auth
        ->staticExpects($this->any())
        ->method('user')
        ->will($this->returnValue(array(
            'id' => '8',
            'group_id' => '1')));
        
        return $InstantSurvery;
    }
    
    
    public function testAddSurvery() 
    {
        $InstantSurvery = $this->generate(
            'InstantSurvery', array(
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_startBackendWorker',
                    ),
                'components' => array(
                    'Auth' => array('user'),
                    )
                )
            );
        
        $InstantSurvery->Auth
        ->staticExpects($this->any())
        ->method('user')
        ->will($this->returnValue(array(
            'id' => '8',
            'group_id' => '1')));
        
        $InstantSurvery
        ->expects($this->once())
        ->method('_startBackendWorker')
        ->will($this->returnValue(true));
        
        $data = array(
            'Program' => array(
                'name' => 'programName',
                'url' => 'programurl',
                'database'=> 'programdatabase'
                )
            );
        
        $this->testAction('/InstantSurvery/addSurvery.json', array('data' => $data, 'method' => 'post'));
    }
    
}
