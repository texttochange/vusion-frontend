<?php
App::uses('ProgramSetting', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class ProgramSettingTestCase extends CakeTestCase
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
        
        $options              = array('database' => 'test');
        $this->ProgramSetting = new ProgramSetting($options);
    
        $this->ProgramSetting->setDataSource('mongo_test');
        
        $this->mongodb =& ConnectionManager::getDataSource($this->ProgramSetting->useDbConfig);
        $this->mongodb->connect();
        
        $this->dropData();
        
    }
    
    
    public function tearDown()
    {
        unset($this->ProgramSetting);
        
        parent::tearDown();
    }


    public function dropData()
    {        
        $this->ProgramSetting->deleteAll(true,false);
    }

    
    public function testGetProgramSetting_notInDatabase()
    {         
        $result = $this->ProgramSetting->find('getProgramSetting', array(
            'key'=>'shortcode', 
            'value' => '8282'
            ));
        
        $this->assertEquals(array(), $result);    
    }
    

    public function testGetProgramSetting_searchNullInDatabase()
    {         
        $result = $this->ProgramSetting->find('getProgramSetting', array(
            'key'=>'shortcode', 
            'value' => null
            ));
        
        $this->assertEquals(array(), $result);    
    }

    
}

