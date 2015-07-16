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


class TestStatsComponentController extends Controller
{

    var $components = array('Stats');

    function constructClasses()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
        $this->redisProgramPrefix = 'unittest';
    }
    
}


class StatsComponentTest extends CakeTestCase
{

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
        $this->StatsComponent->startup($this->Controller);
  
        $this->redis = $this->Controller->redis;
        
        $this->instanciateModels(
            array('Participant', 'Schedule', 'History', 'ProgramSetting'), 
            'testdbprogram');
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');

        $this->Maker = new ScriptMaker();
    }
    
    
    protected function dropData()
    {
        $this->Participant->deleteAll(true, false);
        $this->Schedule->deleteAll(true, false);
        $this->History->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
    }
    
    
    protected function instanciateModels($modelNames, $options)
    {
       foreach ($modelNames as $modelName) {
            $this->{$modelName} = ProgramSpecificMongoModel::init($modelName, $options, true);
        }
    }
    
    
    public function tearDown()
    {
        $keys = $this->redis->keys('unittest*');
        foreach ($keys as $key){
            $this->redis->delete($key);
        }
        $this->dropData();
        unset($this->StatsComponent);
        parent::tearDown();
    }
    
    
    public function mkStats()
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
    
    
    public function testGetStats()
    {
        $this->redisProgramPrefix = 'unittest';        
        $testStats = $this->mkStats();
        $program = array(
            'Program'=> array(
                'database' => 'testdbprogram'));
        $key = $this->redisProgramPrefix.':'.$program['Program']['database'].':stats';
        
        $this->redis->set($key, json_encode($testStats));
        $programTestStats = $this->StatsComponent->getProgramStats($program['Program']['database']);
        
        $this->assertEqual(
            $testStats,
            $programTestStats
            );
    }
        
    
    public function testGetStats_noStatsInRedis()
    {
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($this->Maker->getParticipant());
        $this->Schedule->create('dialogue-schedule');
        $saveSchedule = $this->Schedule->save(array(
            'Schedule' => array(
                'participant-phone' => '+256712747841',
                'dialogue-id' => 'def456')
            )
            ); 
        $history_1 = array(
            'object-type' => 'unattach-history',
            'participant-phone' => '256712747841',
            'message-content' => 'Hello everyone!',
            'timestamp' => '2012-02-08T12:20:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_1);
        $savedHistory = $this->History->save($history_1);

        $history_2 = array(
            'object-type' => 'unattach-history',
            'participant-phone' => '256712747842',
            'message-content' => 'Hello everyone!',
            'timestamp' => '2012-03-08T12:20:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_2);
        $savedHistory2 = $this->History->save($history_2);
        
        $key = "unittest:testdbprogram:stats";
        
        $stats = $this->redis->get($key);
        $programTestStats = $this->StatsComponent->getProgramStats('testdbprogram');
        
        $this->assertEqual(
            null,
            $stats
            );
        
        $this->assertEqual(
            '2',
            $programTestStats['history-count']
            );
    }
    
    public function testGetStats_mongoTimeout_fewSeconds()
    {
        $dummyHistory = $this->getMock('Model', array('count'));
        $dummyHistory
        	->expects($this->once())
        	->method('count')
        	->will($this->throwException(new Exception));
        	
        $programTestStats = $this->StatsComponent->getProgramStat($dummyHistory, $onlyCached=true);
        
        $this->assertEqual('N/A', $programTestStats);
    }
    

    public function testGetTimeToCacheStatsExpire()
    {
        $this->StatsComponent->cacheStatsExpire = array(
            1000 => 30,
            2000 => 60,
            3000 => 120);

        $this->assertEqual(
           30,
           $this->StatsComponent->_getTimeToCacheStatsExpire(100));

        $this->assertEqual(
           60,
           $this->StatsComponent->_getTimeToCacheStatsExpire(1100));
        
        $this->assertEqual(
           60,
           $this->StatsComponent->_getTimeToCacheStatsExpire(2000));

        $this->assertEqual(
           120,
           $this->StatsComponent->_getTimeToCacheStatsExpire(4000));
    }

    
    public function testGetStats_noCachedStats()
    {       
        $programTestStats = $this->StatsComponent->getProgramStats('mydbprogram', true);
        
        $this->assertEqual(
            null,
            $programTestStats);
    }

}
