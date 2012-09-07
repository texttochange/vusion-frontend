<?php
App::uses('ShortCode', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class ShortCodeTestCase extends CakeTestCase
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
            $config = new DATABASE_CONFIG();
            $this->_config = $config->test;
        }
        
        ConnectionManager::create('mongo_test', $this->_config);
        $this->Mongo = new MongodbSource($this->_config);

        $option         = array('database'=>'test');
        $this->ShortCode = new ShortCode($option);

        $this->ShortCode->setDataSource('mongo_test');
        $this->ShortCode->deleteAll(true, false);
    }


    public function tearDown()
    {
        $this->ShortCode->deleteAll(true, false);
        unset($this->ShortCode);
        parent::tearDown();
    }

    public function testSave()
    {
        $emptyShortCode = array();

        $wrongShortCode = array(
            'shortcode' => '8282',
            'badfield' => 'something'
            );

        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($emptyShortCode);
        $this->assertEqual($emptyShortCode, array());
        
        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($wrongShortCode);
        $this->assertFalse(array_key_exists('badfield', $savedShortCode['ShortCode']));
                     
    }

}