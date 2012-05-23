<?php 
App::uses('Participant', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');

class ParticipantTestCase extends CakeTestCase
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

        $option            = array('database'=>'test');
        $this->Participant = new Participant($option);

        $this->Participant->setDataSource('mongo_test');

        $this->dropData();
    }


    public function tearDown()
    {
        unset($this->Participant);
        parent::tearDown();
    }


    public function dropData()
    {
        $this->Participant->deleteAll(true, false);
    }


    public function testSave()
    {
        //1st assertion phone is already a string
        $participant = array(
            'phone' => '+788601462',
            'name' => 'Oliv'
            );
        $this->Participant->create();

        $savedParticipant = $this->Participant->save($participant);

        $this->assertTrue(is_string($savedParticipant['Participant']['phone']));

        //2nd assertion phone is a number
        $participant = array(
            'phone' => 788601463,
            'name' => 'Oliv'
            );
        $this->Participant->create();

        $savedParticipant = $this->Participant->save($participant);

        $this->assertFalse(is_string($savedParticipant['Participant']['phone']));
    }


}
