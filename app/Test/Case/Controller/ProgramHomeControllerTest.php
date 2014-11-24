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
    
    
    /** Test methods */
    
    public function testIndex_emptyProgram_asManager()
    {
        $this->mockProgramAccess();
        
        $this->testAction("/testurl/home", array('method' => 'get'));
        
        $this->assertEquals($this->vars['programDetails']['name'], $this->programData[0]['Program']['name']);
        $this->assertEquals($this->vars['programDetails']['url'], $this->programData[0]['Program']['url']);        
    }
    
    /*
    public function testIndex_displayScheduled()
    {
    $this->mockProgramAccess();
    
    $dialogue = $this->ScriptMaker->getOneDialogue(); 
    
    $savedDialogue = $this->Home->Dialogue->saveDialogue($dialogue['Dialogue']);
    $this->Home->Dialogue->makeActive($savedDialogue['Dialogue']['_id']);
    
    $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
    $timeNow = $this->ProgramSetting->getProgramTimeNow();
    
    $timeToSend = $timeNow->modify("+6 hour");
    $unattachedMessage = array(
    'type-schedule' => 'fixed-time',
    'fixed-time' => $timeToSend->format('d/m/Y H:i'),
    'content' => 'Hello',
    'name' => 'test',
    'send-to-type' => 'all',
    'created-by' => 2
    );        
    
    $this->Home->UnattachedMessage->create('unattached-message');
    $savedUnattachMessage = $this->Home->UnattachedMessage->save($unattachedMessage);
    
    $schedules = array(
    array(
    'object-type' => 'dialogue-schedule',
    'date-time' => $timeToSend->format(DateTime::ISO8601),
    'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
    'interaction-id' => $savedDialogue['Dialogue']['interactions'][0]['interaction-id'],
    ),
    array(
    'object-type' => 'unattach-schedule',
    'date-time' => $timeToSend->format(DateTime::ISO8601),
    'unattach-id' => $savedUnattachMessage['UnattachedMessage']['_id'],
    )
    );
    
    foreach ($schedules as $schedule){
    $this->Home->Schedule->create($schedule['object-type']);
    $this->Home->Schedule->save($schedule);
    }
    
    $this->testAction("/testurl/home", array('method' => 'get'));
    
    $this->assertEquals(2, count($this->vars['schedules']));
    $this->assertEquals("how are you?", $this->vars['schedules'][0]['content']);
    $this->assertEquals(1, $this->vars['schedules'][0]['csum']);
    $this->assertEquals("Hello", $this->vars['schedules'][1]['content']);
    $this->assertEquals(1, $this->vars['schedules'][1]['csum']);
    }
    */
    
}
