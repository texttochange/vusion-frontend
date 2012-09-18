<?php
App::uses('Dialogue', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');
App::uses('Schedule', 'Model');
App::uses('ScriptMaker', 'Lib');


class DialogueTestCase extends CakeTestCase
{
    
    public function setUp()
    {
        parent::setUp();

        $option         = array('database'=>'testdbprogram');
        $this->Dialogue = new Dialogue($option);
        $this->Schedule = new Schedule($option);

        $this->Maker = new ScriptMaker();
    }


    public function tearDown()
    {
        $this->Dialogue->deleteAll(true, false);
        $this->Schedule->deleteAll(true, false);
        unset($this->Dialogue);
        unset($this->Schedule);
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
        
        $this->Dialogue->saveDialogue($saveActiveFirstVersion);
        $this->assertEquals(2, $this->Dialogue->find('count'));
        
        $this->Dialogue->saveDialogue($saveActiveFirstVersion);
        $this->assertEquals(2, $this->Dialogue->find('count'));
        
        $saveActiveSecondVersion = $this->Dialogue->makeDraftActive($saveDraftFirstVersion['Dialogue']['dialogue-id']);
        $this->assertEquals(1, count($this->Dialogue->getActiveDialogues()));

        //adding a new Dialogue
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

        //active the new Dialogue
        $saveActiveOtherDialogue = $this->Dialogue->makeDraftActive($saveDraftOtherDialogue['Dialogue']['dialogue-id']);
        $this->assertEquals(2, count($this->Dialogue->getActiveDialogues()));
        $this->assertEquals(2, count($this->Dialogue->getDialogues()));
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertTrue($activeAndDraft[0]['Active']!=0);
        $this->assertTrue($activeAndDraft[0]['Draft']==0);
        $this->assertTrue($activeAndDraft[1]['Active']!=0);
        $this->assertTrue($activeAndDraft[1]['Draft']==0);

        //add new version of the dialogue and check we get the correct one
        $data['Dialogue']['dialogue-id'] = $saveActiveOtherDialogue['Dialogue']['dialogue-id']; 
        $data['Dialogue']['do'] = "something new";
        $saveNewVersionOtherDialogue = $this->Dialogue->saveDialogue($data);
        $this->Dialogue->makeDraftActive($saveNewVersionOtherDialogue['Dialogue']['dialogue-id']);
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertEquals($saveNewVersionOtherDialogue['Dialogue']['_id'], $activeAndDraft[1]['Active']['_id']."");
        
        //reactivate the olderone
        $this->Dialogue->makeActive($saveActiveOtherDialogue['Dialogue']['_id']);
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertEquals($saveActiveOtherDialogue['Dialogue']['_id'], $activeAndDraft[1]['Active']['_id']);

    }


    public function testMakeDialogueActive_deleteNonPresentInteractions()
    {
         $savedDialogue = $this->Dialogue->saveDialogue($this->Maker->getOneDialogue());
         $schedule = $this->Maker->getDialogueSchedule(
             '08',
             $savedDialogue['Dialogue']['dialogue-id'],
             '01'
             );
         $this->Schedule->create($schedule['Schedule']['object-type']);
         $this->Schedule->save($schedule['Schedule']);
         
         $this->Dialogue->makeDraftActive($savedDialogue['Dialogue']['dialogue-id']);
         
         $this->assertEqual(0, $this->Schedule->find('count'));
         
    }


    public function testValidate_date_ok()
    {
        $data['Dialogue'] = array(
            'interactions' => array(
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
        $this->assertEqual($result[0]['Dialogue']['interactions']['date-time'], '2012-06-04T10:30:00');
        $this->assertEqual($result[0]['Dialogue']['interactions']['sub-tree']['date-time'], '2012-06-04T10:31:00');
        $this->assertEqual($result[0]['Dialogue']['interactions']['another-sub-tree']['date-time'], '2012-06-04T10:32:00');
        $this->assertEqual($result[0]['Dialogue']['interactions']['again-sub-tree']['date-time'], '2012-06-04T10:33:00');
    }


    public function testValidate_date_fail()
    {
        $data['Dialogue'] = array(
            'interactions' => array(
                'date-time' => '2012-06-04 10:30:00',
                )
            );    
        $saveResult = $this->Dialogue->saveDialogue($data['Dialogue']);
        $this->assertFalse(!empty($saveResult) && is_array($saveResult));    
    }

    public function testFindAllKeywordInDialogues()
    {
        $dialogueOne['Dialogue'] = array(
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
            );

        $dialogueTwo['Dialogue'] = array(            
            'interactions'=> array(
                array(
                    'type-interaction' => 'question-answer', 
                    'content' => 'how are you', 
                    'keyword' => 'FEL', 
                    )
                )
            );

      
        $saveDialogueOne = $this->Dialogue->saveDialogue($dialogueOne['Dialogue']);
        $this->Dialogue->makeDraftActive($saveDialogueOne['Dialogue']['dialogue-id']);    

        $saveDialogueTwo = $this->Dialogue->saveDialogue($dialogueTwo['Dialogue']);
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

    
    public function testDeleteDialogue()
    {
         $dialogueOne['Dialogue'] = array(
             'name'=> 'mydialgoue',
             'dialogue-id'=> '01'
             );
         $schedule['Schedule'] = array(
             'dialogue-id'=>'01',
             'interaction-id'=>'01',
             );   
         $dialogueTwo['Dialogue'] = array(
             'name'=> 'mydialgoue',
             'dialogue-id'=> '02'
             );
         $this->Dialogue->saveDialogue($dialogueOne);
         $this->Schedule->create('dialogue-schedule');
         $this->Schedule->save($schedule);
         $this->Dialogue->saveDialogue($dialogueTwo);
         
         $this->Dialogue->deleteDialogue('01');
         
         $dialogues = $this->Dialogue->getActiveAndDraft();
         $this->assertEqual(1, count($dialogues));
         $this->assertEqual('02', $dialogues[0]['dialogue-id']);

         $this->assertEqual(0, $this->Schedule->find('count'));
    }

}
