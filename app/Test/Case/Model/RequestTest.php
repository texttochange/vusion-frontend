<?php
App::uses('Request', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class RequestTestCase extends CakeTestCase
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
        $this->Request = new Request($option);

        $this->Request->setDataSource('mongo_test');
        $this->Request->deleteAll(true, false);
    }


    public function tearDown()
    {
        $this->Request->deleteAll(true, false);
        unset($this->Request);
        parent::tearDown();
    }

    
    public function testSave()
    {
        $this->assertTrue(false);
        
    }

}
