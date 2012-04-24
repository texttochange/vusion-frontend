<?php

App::uses('Schedule', 'Model');


class ScheduleTestCase extends CakeTestCase
{

     protected $_config = array(
        'datasource' => 'Mongodb.MongodbSource',
        'host' => 'localhost',
        'login' => '',
        'password' => '',
        'database' => 'test',
        'port' => 27017,
        'prefix' => '',
        'persistent' => true,
    );

    public function setUp()
    {
        parent::setUp();
        
         $connections = ConnectionManager::enumConnectionObjects();
        
        if (!empty($connections['test']['classname']) && $connections['test']['classname'] === 'mongodbSource'){
            $config        = new DATABASE_CONFIG();
            $this->_config = $config->test;
        }
        
        ConnectionManager::create('mongo_test', $this->_config);
        $this->Mongo = new MongodbSource($this->_config);

        $options        = array('database' => 'test');
        $this->Schedule = new Schedule($options);

        $this->Schedule->setDataSource('mongo_test');
        
        $this->mongodb =& ConnectionManager::getDataSource($this->Schedule->useDbConfig);
        $this->mongodb->connect();
            
    }

    public function tearDown()
    {
        $this->Schedule->deleteAll(true, false);
        unset($this->Schedule);
        parent::tearDown();
    }

    public function testGetScheduleNextSummary()
    {

         $schedules = array(
             array(
                 'dialogue-id' => 'someId',
                 'interaction-id' => 'someOtherId',
                 'datetime' => '2013-04-12T12:00',
                 'participant-phone' => '06'
                 ),
             array(
                 'dialogue-id' => 'someId',
                 'interaction-id' => 'someOtherId',
                 'datetime' => '2013-04-12T12:00',
                 'participant-phone' => '07'
                 ),
             array(
                 'dialogue-id' => 'someId',
                 'interaction-id' => 'someOtherId2',
                 'datetime' => '2013-04-12T13:00',
                 'participant-phone' => '07'
                 ),
             array(
                  'unattach-id' => 'someId2',
                  'datetime' => '2013-04-12T11:00',
                 ),
              array(
                  'content-type' => 'feedback',
                 )
             );

         foreach ($schedules as $schedule) {
             $this->Schedule->create();
             $this->Schedule->save($schedule);
         }

         $result = $this->Schedule->summary();
         $this->assertEquals("2", $result[0]['csum']);
         $this->assertEquals("1", $result[1]['csum']);
         $this->assertEquals("1", $result[2]['csum']); 
    }

}
