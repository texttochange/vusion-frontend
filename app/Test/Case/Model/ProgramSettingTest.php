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
        
        $this->assertNull($result);    
    }
    

    public function testGetProgramSetting_searchNullInDatabase()
    {         
        $result = $this->ProgramSetting->find('getProgramSetting', array(
            'key'=>'shortcode', 
            'value' => null
            ));
        
        $this->assertNull($result);    
    }


    public function testSaveProgramSetting()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->assertEqual(1, count($this->ProgramSetting->find('count')));
        $this->assertEqual('8282', $this->ProgramSetting->find('getProgramSetting', array('key'=>'shortcode')));        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8181');
        $this->assertEqual(1, count($this->ProgramSetting->find('count')));
        $this->assertEqual('8181', $this->ProgramSetting->find('getProgramSetting', array('key'=>'shortcode')));        

    }

    public function testGetProgramSettings()
    {
        $this->assertEqual(0, count($this->ProgramSetting->getProgramSettings()));
        $this->ProgramSetting->saveProgramSetting('shortcode', 'value1');
        $this->ProgramSetting->saveProgramSetting('timezone', 'value2');
        $this->assertEqual(
            array('shortcode' => 'value1', 'timezone'=>'value2'),
            $this->ProgramSetting->getProgramSettings()
            ); 
    }

    public function testGetProgramTimeNow()
    {
        $this->assertNull($this->ProgramSetting->getProgramTimeNow());        

        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $this->assertNotNull($this->ProgramSetting->getProgramTimeNow());
       
    }

    public function testIsNotPast()
    {
        
        $now = new DateTime('now');
        date_timezone_set($now, timezone_open('Africa/Kampala'));        
        $past = $now->modify('-1 hours');        

        $this->assertEqual(
            "The program settings are incomplete. Please specificy the Timezone.", 
            $this->ProgramSetting->isNotPast($past));        

        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        
        $this->assertFalse($this->ProgramSetting->isNotPast($past));
        $future = $now->modify('+1 hours');
        $this->assertTrue($this->ProgramSetting->isNotPast($future));
    }

    
}

