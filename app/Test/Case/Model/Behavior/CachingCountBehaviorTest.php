<?php
App::uses('History', 'Model');

class CachingCountBehaviorTest extends CakeTestCase 
{


    public function setUp()
    {
        $this->settings = array(
            'redis' => array('host' => '127.0.0.1', 'port' => '6379'), 
            'redisPrefix' => array('base' => 'vusion', 'programs' => 'programs'),
            'cacheExpire' => array(
                1 => 30,          #1sec cache 30sec
                5 => 240          #5sec cache  4min
                ));

        $this->database = 'testdbprogram';

        $this->redis = new Redis();
		$this->redis->connect(
		    $this->settings['redis']['host'],
		    $this->settings['redis']['port']);
		$this->dropData();
    }


    public function tearDown()
    {
        $this->dropData();
    }

    protected function dropData() 
    {
        $keys = $this->redis->keys('vusion:programs:'. $this->database.':cachedcounts:*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }


    public function testCount()
    {
        $testModel = new History(array('database' => $this->database));
        $testModel->Behaviors->load('CachingCount', $this->settings);
        $result = $testModel->count(array('participant-phone' => '+25677', 'message-direction' => 'incomming'));
        $this->assertEqual($result, 0);
        $keys = $this->redis->keys('vusion:programs:'. $this->database.':cachedcounts:*');
        $this->assertEqual(count($keys), 1);        


        $result = $testModel->count(array('message-direction' => 'incomming', 'participant-phone' => '+25677'));
        $this->assertEqual($result, 0);
        $keys = $this->redis->keys('vusion:programs:'. $this->database.':cachedcounts:*');
        $this->assertEqual(count($keys), 1); // same condition so only one should be stored
        foreach($keys as $key) {
            $this->assertEqual(30000, $this->redis->ttl($key));
        }
    }


    public function testCount_limitedCount_notCached()
    {
        $testModel = new History(array('database' => $this->database));
        $testModel->Behaviors->load('CachingCount', $this->settings);
        $result = $testModel->count(true, 10);
        $this->assertEqual($result, 0);
        $this->assertFalse($this->redis->exists('vusion:programs:testdbprogram:cachedcounts:History:b:1;'));
    }


}
