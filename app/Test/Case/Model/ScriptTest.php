<?php
App::uses('Script', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class ScriptTestCase extends CakeTestCase 
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
        
        $options      = array('database' => 'test');
        $this->Script = new Script($options);
    
        $this->Script->setDataSource('mongo_test');
        
        $this->mongodb =& ConnectionManager::getDataSource($this->Script->useDbConfig);
        $this->mongodb->connect();
        
        $this->dropData();    
    }


    public function tearDown()
    {
        unset($this->Script);
        
        parent::tearDown();
    }


    public function dropData()
    {
        $this->Script->deleteAll(true,false);
    }


    public function testValidate_date_ok()
    {
        $data['Script'] = array(
            'script' => array(
                'date-time' => '04/06/2012 10:30',
                'sub-tree' => array( 
            	   'date-time' => '04/06/2012 10:31',
            	   ),
            	'another-sub-tree' => array(
            	    'date-time' => '2012-06-04T10:32:00',
            	    ),
            	'again-sub-tree' => array(
            		'date-time' => '04/06/2012 10:33',
            	   )
            	)
            );    
        $this->Script->recursive = -1;
        $this->Script->create();
        $saveResult = $this->Script->save($data);
        $this->assertTrue(!empty($saveResult) && is_array($saveResult));
    
        $result = $this->Script->find('all');
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['Script']['script']['date-time'], '2012-06-04T10:30:00');
        $this->assertEqual($result[0]['Script']['script']['sub-tree']['date-time'], '2012-06-04T10:31:00');
        $this->assertEqual($result[0]['Script']['script']['another-sub-tree']['date-time'], '2012-06-04T10:32:00');
        $this->assertEqual($result[0]['Script']['script']['again-sub-tree']['date-time'], '2012-06-04T10:33:00');
    }

    public function testValidate_date_fail()
    {
        $data['Script'] = array(
            'script' => array(
            	    'date-time' => '2012-06-04 10:30:00',
            	    )
            );    
        $this->Script->recursive = -1;
        $this->Script->create();
        $saveResult = $this->Script->save($data);
        $this->assertFalse(!empty($saveResult) && is_array($saveResult));    
    }


    public function testFindCountDraft() 
    {        
        $result = $this->Script->find('countDraft');
        //print_r($result);
        $this->assertEquals(count($result), 0);
   
        $data['Script'] = array(
            'script' => array()
            );
        
        $this->Script->recursive = -1;
        $this->Script->create();
        $this->Script->save($data);
        
        $result = $this->Script->find('countDraft');
        //print_r($result);
        $this->assertEquals(count($result), 1);
    
        $this->Script->create();
        $this->Script->save($data);

        $result = $this->Script->find('countDraft');        
        $this->assertEquals(count($result), 2);    
    }


    public function testMakeTurnDraftActive()
    {
        $data['Script'] = array(
            'script' => array(
                'do' => 'something'
                )
            );
        $this->Script->recursive = -1;
        $this->Script->create();
        $id = $this->Script->save($data);
        
        $draft = $this->Script->find('draft');
        $this->assertEquals($draft[0]['Script']['activated'], 0);
        
        $this->Script->makeDraftActive();
        
        $this->assertEquals(count($this->Script->find('draft')), 0);
        
        $active = $this->Script->find('active');
        $this->assertEquals($active[0]['Script']['activated'], 1);
        $this->assertEquals($active[0]['Script']['script'], $data['Script']['script']);    
    }


    public function testMakeTurnDraftActive_noDraft()
    {
        $result = $this->Script->makeDraftActive();
        $this->assertEquals($result, false);
    }


    public function testFindAllKeywordInScript()
    {
        $data['Script'] = array(
            'script' => array(
                'dialogues' => array(
                    array(
                        'dialogue-id'=> 'script.dialogues[0]',
                        'interactions'=> array(
                            array(
                                'type-interaction' => 'annoucement', 
                                'content' => 'hello', 
                                'interaction-id' => 'script.dialogues[0].interactions[0]'
                                ),
                            array(
                                'type-interaction' => 'question-answer', 
                                'content' => 'how are you', 
                                'keyword' => 'FEEL', 
                                'interaction-id' => 'script.dialogues[0].interactions[1]'
                                ),
                            array( 
                                'type-interaction'=> 'question-answer', 
                                'content' => 'What is you name?', 
                                'keyword'=> 'NAME', 
                                'interaction-id'=> 'script.dialogues[0].interactions[2]'
                                )
                            )
                        )
                    )
                )
            );
        $this->Script->recursive = -1;
        $this->Script->create();
        $id = $this->Script->save($data);
        $this->Script->makeDraftActive();    

        $result = $this->Script->find('hasKeyword', array('keyword'=>'FEEL'));
        $this->assertEquals(1, count($result));

        $result = $this->Script->find('hasKeyword', array('keyword'=>'NAME'));
        $this->assertEquals(1, count($result));      

        $result = $this->Script->find('hasKeyword', array('keyword'=>'BT'));
        $this->assertEquals(0, count($result));      
    }

}
