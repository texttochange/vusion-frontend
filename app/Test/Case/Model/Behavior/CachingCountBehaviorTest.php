<?php
App::uses('History', 'Model');

class CachingCountBehaviorTest extends CakeTestCase 
{


    public function setUp()
    {
        $this->settings = array(
            'redis' => array('host' => '127.0.0.1', 'port' => '6379'), 
            'redisPrefix' => array('base' => 'vusion', 'programs' => 'programs'),
            'cacheCountExpire' => array(
                1 => 30,          #1sec cache 30sec
                5 => 240          #5sec cache  4min
                ));

        $this->database = 'testdbprogram';

        $this->redis = new Redis();
		$this->redis->connect(
		    $this->settings['redis']['host'],
		    $this->settings['redis']['port']);
		
		Configure::write('vusion.redis', $this->settings['redis']);
		Configure::write('vusion.redisPrefix', $this->settings['redisPrefix']);
		Configure::write('vusion.cacheCountExpire', $this->settings['cacheCountExpire']);
		
		$this->Model = new History(array('database' => $this->database));

		$this->dropData();
    }


    public function tearDown()
    {
        $this->dropData();
    }

    protected function dropData() 
    {
        $this->Model->deleteAll(true, false);
        $keys = $this->redis->keys('vusion:programs:'. $this->database.':cachedcounts:*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }


    public function testCount_sameConditions_sameKey()
    {
        $result = $this->Model->count(array('participant-phone' => '+25677', 'message-direction' => 'incomming'));
        $this->assertEqual($result, 0);
        $keys = $this->redis->keys('vusion:programs:'. $this->database.':cachedcounts:*');
        $this->assertEqual(count($keys), 1);        

        //same condition, only one key should be stored
        $result = $this->Model->count(array('message-direction' => 'incomming', 'participant-phone' => '+25677'));
        $this->assertEqual($result, 0);
        $keys = $this->redis->keys('vusion:programs:'. $this->database.':cachedcounts:*');
        $this->assertEqual(count($keys), 1);
        foreach($keys as $key) {
            $this->assertEqual(30000, $this->redis->ttl($key));
        }
    }


    public function testCount_differentConditions_differentConditions()
    {
        $history = array(
            'object-type' => 'unattach-history',
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-direction' => 'outgoing', 
            'message-status' => 'pending',
            'unattach-id' =>'5'
            );
        $this->Model->create('unattach-history');
        $saveHistory = $this->Model->save($history);  

        $result = $this->Model->count();
        $this->assertEqual($result, 1);
        $keys = $this->redis->keys('vusion:programs:'. $this->database.':cachedcounts:*');
        $this->assertEqual(count($keys), 1);

        $result = $this->Model->count(array('message-direction' => 'incomming'));
        $this->assertEqual($result, 0);
        $keys = $this->redis->keys('vusion:programs:'. $this->database.':cachedcounts:*');
        $this->assertEqual(count($keys), 2); 
    }


    public function testCount_limitedCount_notCached()
    {
        $result = $this->Model->count(true, 10);
        $this->assertEqual($result, 0);
        $keys = $this->redis->keys('vusion:programs:'. $this->database.':cachedcounts:*');
        $this->assertEqual(count($keys), 0); 
    }

}
