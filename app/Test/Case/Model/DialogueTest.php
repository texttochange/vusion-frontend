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
        $this->Dialogue->deleteAll(true, false);
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
                'do' => 'something'
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

        /**adding a new Dialogue*/
        unset($data['Dialogue']['dialogue-id']);
        $saveDraftOtherDialogue = $this->Dialogue->saveDialogue($data);
        $this->assertEquals(1, count($this->Dialogue->getActiveDialogues()));
        $this->assertEquals(2, count($this->Dialogue->getDialogues()));
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertEquals(2, count($activeAndDraft));
        $this->assertTrue($activeAndDraft[0]['Active']!=0);
        $this->assertTrue($activeAndDraft[0]['Draft']==0);
        $this->assertTrue($activeAndDraft[1]['Active']==0);
        $this->assertTrue($activeAndDraft[1]['Draft']!=0);

        /**active the new Dialogue*/
        $saveActiveOtherDialogue = $this->Dialogue->makeDraftActive($saveDraftOtherDialogue['Dialogue']['dialogue-id']);
        $this->assertEquals(2, count($this->Dialogue->getActiveDialogues()));
        $this->assertEquals(2, count($this->Dialogue->getDialogues()));
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertTrue($activeAndDraft[0]['Active']!=0);
        $this->assertTrue($activeAndDraft[0]['Draft']==0);
        $this->assertTrue($activeAndDraft[1]['Active']!=0);
        $this->assertTrue($activeAndDraft[1]['Draft']==0);

        /**add new version of the dialogue and check we get the correct one*/
        $data['Dialogue']['dialogue-id'] = $saveActiveOtherDialogue['Dialogue']['dialogue-id']; 
        $data['Dialogue']['do'] = "something new";
        $saveNewVersionOtherDialogue = $this->Dialogue->saveDialogue($data);
        $this->Dialogue->makeDraftActive($saveNewVersionOtherDialogue['Dialogue']['dialogue-id']);
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertEquals($saveNewVersionOtherDialogue['Dialogue']['_id'], $activeAndDraft[1]['Active']['_id']);
        
        /**reactivate the olderone*/
        $this->Dialogue->makeActive($saveActiveOtherDialogue['Dialogue']['_id']);
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertEquals($saveActiveOtherDialogue['Dialogue']['_id'], $activeAndDraft[1]['Active']['_id']);
       

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


    public function saveAddingId()
    {
        $dialogueOne['Dialogue'] = array(
            'interactions'=> array(
                array(
                    'type-interaction' => 'question-answer', 
                    'content' => 'how are you', 
                    'keyword' => 'FEEL', 
                    ),
                array(
                    'type-interaction' => 'question-answer', 
                    'content' => 'How old are you?', 
                    'keyword' => 'Age', 
                    ),
                array( 
                    'type-interaction'=> 'question-answer', 
                    'content' => 'What is you name?', 
                    'keyword'=> 'NAME', 
                    'interaction-id' => 'id not to be removed'
                    )
                )
            );

      
        $saveDialogueOne = $this->Dialogue->saveDialogue($dialogueOne);
        $this->assertTrue(isset($dialogueOne['Dialogue']['dialogue-id']));
        $this->assertTrue(isset($dialogueOne['Dialogue']['interactions'][0]['interaction-id']));
        $this->assertFalse($dialogueOne['Dialogue']['interactions'][0]['interaction-id']!=
            $dialogueOne['Dialogue']['interactions'][1]['interaction-id']);
        $this->assertEquals('id not to be removed', isset($dialogueOne['Dialogue']['interactions'][2]['interaction-id']));      
    }

}
