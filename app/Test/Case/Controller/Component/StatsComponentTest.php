<?php 
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('StatsComponent', 'Controller/Component');



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
	}
	
	public function tearDown()
	{
		$keys = $this->redis->keys('vusion:programs:*');
		foreach ($keys as $key){
			$this->redis->delete($key);
		}
		
	}
	
	public function mockStats()
	{
		return array(
			'active-participant-count' => '2',
			'participant-count' => '2',
			'all-received-messages-count'=> '2',
			'current-month-received-messages-count' => '2',
			'all-sent-messages-count' => '2',
			'current-month-sent-messages-count' => '2',
			'total-current-month-messages-count' => '2',
			'history-count' => '2',
			'today-schedule-count' => '2',
			'schedule-count' => '2',
			'object-type' => 'program-stats',
			'model-version'=> '1'
			);
		
	}
	
	public function testGetStats()
	{
		$Stats = $this->mockStats();
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
		$key = "vusion:programs:test1:stats";
		$stats = $this->redis->get($key);

		$this->assertEqual(
			null,
			$stats);
	}
}
?>
