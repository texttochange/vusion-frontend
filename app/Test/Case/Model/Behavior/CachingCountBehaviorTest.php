<?php
App::uses('History', 'Model');

class CachingCountBehaviorTest extends CakeTestCase 
{


    public function setUp()
    {
        $this->settings = array(
            'redis' => array(
                'host' => '127.0.0.1', 'port' => '6379'), 
            'redisPrefix' => 'vusion:programs',
            'cacheExpire' => array(
                1 => 30,          #1sec cache 30sec
                5 => 240          #5sec cache  4min
                ));

        $this->database = 'testdbprogram';

        $this->redis = new Redis();
		$this->redis->connect(
		    $this->settings['redis']['host'],
		    $this->settings['redis']['port']);
    }


    public function tearDown()
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
        $result = $testModel->count();
        $this->assertEqual($result, 0);
        $this->assertTrue($this->redis->exists('vusion:programs:testdbprogram:cachedcounts:b:1;'));
        $this->assertEqual(
            30000,
            $this->redis->ttl('vusion:programs:testdbprogram:cachedcounts:b:1;'));
    }


    public function testCount_limitedCount_notCached()
    {
        $testModel = new History(array('database' => $this->database));
        $testModel->Behaviors->load('CachingCount', $this->settings);
        $result = $testModel->count(true, 10);
        $this->assertEqual($result, 0);
        $this->assertFalse($this->redis->exists('vusion:programs:testdbprogram:cachedcounts:b:1;'));
    }


}
