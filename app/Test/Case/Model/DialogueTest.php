<?php
App::uses('Dialogue', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class DialogueTestCase extends CakeTestCase
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
        $this->Dialogue = new Dialogue($option);

        $this->Dialogue->setDataSource('mongo_test');

    }


    public function tearDown()
    {
        $this->Dialogue->deleteAll(true, false);
        unset($this->Dialogue);
        parent::tearDown();
    }


    public function testSaveDialogue()
    {
        $data['Dialogue'] = array(
            'dialogue' => array(
                'do' => 'something'
                )
            );
    
        $saveDraftFirstVersion = $this->Dialogue->saveDialogue($data);
        $this->assertEquals(0, $saveDraftFirstVersion['Dialogue']['activated']);
        $saveActiveFirstVersion = $this->Dialogue->makeDraftActive($saveDraftFirstVersion['Dialogue']['dialogue-id']);
        $this->assertEquals(1, $saveActiveFirstVersion['Dialogue']['activated']);
        
        $data['Dialogue']['dialogue-id'] = $saveDraftFirstVersion['Dialogue']['dialogue-id'];
        $this->Dialogue->saveDialogue($data);
        $this->assertEquals(2, $this->Dialogue->find('count'));
        
        $this->Dialogue->saveDialogue($data);
        $this->assertEquals(2, $this->Dialogue->find('count'));
        
        $saveActiveSecondVersion = $this->Dialogue->makeDraftActive($saveDraftFirstVersion['Dialogue']['dialogue-id']);
        $this->assertEquals(1, count($this->Dialogue->getActiveDialogues()));

        unset($data['Dialogue']['dialogue-id']);
        $saveDraftOtherDialogue = $this->Dialogue->saveDialogue($data);
        $this->assertEquals(1, count($this->Dialogue->getActiveDialogues()));
        $this->assertEquals(2, count($this->Dialogue->getDialogues()));
        $saveActiveOtherDialogue = $this->Dialogue->makeDraftActive($saveDraftOtherDialogue['Dialogue']['dialogue-id']);
        $this->assertEquals(2, count($this->Dialogue->getActiveDialogues()));
        $this->assertEquals(2, count($this->Dialogue->getDialogues()));
    }


    public function testValidate_date_ok()
    {
        $data['Dialogue'] = array(
            'dialogue' => array(
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

        $saveResult = $this->Dialogue->saveDialogue($data);
        //print_r($saveResult);
        $this->assertTrue(!empty($saveResult) && is_array($saveResult));
    
        $result = $this->Dialogue->find('all');
        $this->assertEqual(1, count($result));
        $this->assertEqual($result[0]['Dialogue']['dialogue']['date-time'], '2012-06-04T10:30:00');
        $this->assertEqual($result[0]['Dialogue']['dialogue']['sub-tree']['date-time'], '2012-06-04T10:31:00');
        $this->assertEqual($result[0]['Dialogue']['dialogue']['another-sub-tree']['date-time'], '2012-06-04T10:32:00');
        $this->assertEqual($result[0]['Dialogue']['dialogue']['again-sub-tree']['date-time'], '2012-06-04T10:33:00');
    }


    public function testValidate_date_fail()
    {
        $data['Dialogue'] = array(
            'dialogue' => array(
                'date-time' => '2012-06-04 10:30:00',
                )
            );    
        $saveResult = $this->Dialogue->saveDialogue($data);
        $this->assertFalse(!empty($saveResult) && is_array($saveResult));    
    }

    public function testFindAllKeywordInDialogues()
    {
        $dialogueOne['Dialogue'] = array(
            'dialogue' => array(                
                'interactions'=> array(
                    array(
                        'type-interaction' => 'question-answer', 
                        'content' => 'how are you', 
                        'keyword' => 'FEEL', 
                        ),
                    array( 
                        'type-interaction'=> 'question-answer', 
                        'content' => 'What is you name?', 
                        'keyword'=> 'NAME', 
                        )
                    )
                
                )
            );

        $dialogueTwo['Dialogue'] = array(
            'dialogue' => array(
                'interactions'=> array(
                    array(
                        'type-interaction' => 'question-answer', 
                        'content' => 'how are you', 
                        'keyword' => 'FEL', 
                        )
                    )
                
                )
            );

      
        $saveDialogueOne = $this->Dialogue->saveDialogue($dialogueOne);
        $this->Dialogue->makeDraftActive($saveDialogueOne['Dialogue']['dialogue-id']);    

        $saveDialogueTwo = $this->Dialogue->saveDialogue($dialogueTwo);
        $this->Dialogue->makeDraftActive($saveDialogueTwo['Dialogue']['dialogue-id']);    


        $result = $this->Dialogue->useKeyword('FEEL');
        $this->assertEquals(1, count($result));

        $result = $this->Dialogue->useKeyword('NAME');
        $this->assertEquals(1, count($result));      

        $result = $this->Dialogue->useKeyword('FEL');
        $this->assertEquals(1, count($result));     

        $result = $this->Dialogue->useKeyword('BT');
        $this->assertEquals(0, count($result));      
    }


}
