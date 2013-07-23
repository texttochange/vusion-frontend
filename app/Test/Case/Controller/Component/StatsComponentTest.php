<?php 
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('StatsComponent', 'Controller/Component');
App::uses('ScriptMaker', 'Lib');
App::uses('Participant', 'Model');
App::uses('Schedule', 'Model');
App::uses('History', 'Model');
App::uses('ProgramSetting', 'Model');



class TestStatsComponentController extends Controller {
	
	var $components = array('Stats');
	
	function constructClasses()
	{
		$this->redis =new Redis();
		$this->redis->connect('127.0.0.1');
	}
}


class TestStatsComponent extends CakeTestCase {
	
	public $StatsComponent = null;
	public $Controller = null;
	public $fixtures = array('app.program','app.group','app.user', 'app.programsUser');

	public function setUp()
	{
		parent::setUp();
		$Collection = new ComponentCollection();
		$this->StatsComponent = new StatsComponent($Collection);
		$CakeRequest = new CakeRequest();
		$CakeResponse = new CakeResponse();
		
		$this->Controller = new TestStatsComponentController($CakeRequest, $CakeResponse);
		$this->Controller->constructClasses();
		$this->StatsComponent->initialize($this->Controller);
		$this->redis = $this->Controller->redis;
		
		
		$this->Maker = new ScriptMaker();
		$options = array('database' => 'testdbprogram');
		
		$this->Participant = new Participant($options);
		$this->Schedule = new Schedule($options);
		$this->History = new History($options);
		$this->ProgramSetting = new ProgramSetting($options); 
	}
	
	public function tearDown()
	{
		$keys = $this->redis->keys('vusion:programs:*');
		foreach ($keys as $key){
			$this->redis->delete($key);
		}
		
		$this->dropData();
		unset($this->StatsComponent);
		//unset($this->Controller);
		parent::tearDown();
		
	}
	
	public function makeStats()
	{
		return array(
			'active-participant-count' => '2',
			'participant-count' => '2',
			'all-received-messages-count'=> '2',
			'current-month-received-messages-count' => '2',
			'all-sent-messages-count' => '2',
			'current-month-sent-messages-count' => '2',
			'total-current-month-messages-count' => '2',
			'history-count' => '1',
			'today-schedule-count' => '2',
			'schedule-count' => '2',
			'object-type' => 'program-stats',
			'model-version'=> '1'
			);
		
	}
	
	public function dropData()
	{
		$this->Participant->deleteAll(true, false);
		$this->Schedule->deleteAll(true, false);
		$this->History->deleteAll(true, false);
	}
	
	public function testGetStats()
	{
		$Stats = $this->makeStats();
		$program = array(
			'Program'=> array(
				'database' => 'test1'));
		$key = "vusion:programs:test1:stats";
		
		$this->redis->set($key, json_encode($Stats));
		$programTestStats = $this->StatsComponent->getProgramStats($program['Program']['database']);
		
		$this->assertEqual(
			$Stats,
			$programTestStats);
	}
	
	public function testGetStats_noStatsInRedis()
	{
		$this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');    
		$this->Participant->create();
		$savedParticipant = $this->Participant->save($this->Maker->getParticipant());
        $this->Schedule->create('dialogue-schedule');
        $saveSchedule = $this->Schedule->save(array(
            'Schedule' => array(
                'participant-phone' => '+256712747841',
                'dialogue-id' => 'def456',
                )
            )); 
		$this->History->create('unattach-history');
        $savedHistory = $this->History->save(array(
            'participant-phone' => '256712747841',
            'message-content' => 'Hello everyone!',
            'timestamp' => '2012-02-08T12:20:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered',
            ));
        $this->History->create('unattach-history');
        $savedHistory2 = $this->History->save(array(
            'participant-phone' => '256712747842',
            'message-content' => 'Hello everyone!',
            'timestamp' => '2012-03-08T12:20:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered',
            ));
        
		$key = "vusion:programs:testdbprogram:stats";
		
		$stats = $this->redis->get($key);
		$programTestStats = $this->StatsComponent->getProgramStats('testdbprogram');
	
		$this->assertEqual(
			null,
			$stats);
		$this->assertEqual(
			null,
			$stats);
	}
}
?>
