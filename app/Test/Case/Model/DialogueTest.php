<?php
App::uses('Dialogue', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');
App::uses('Schedule', 'Model');
App::uses('ScriptMaker', 'Lib');
App::uses('Participant', 'Model');
App::uses('ProgramSetting', 'Model');


class DialogueTestCase extends CakeTestCase
{
    
    public function setUp()
    {
        parent::setUp();

        $option         = array('database'=>'testdbprogram');
        $this->Dialogue = new Dialogue($option);
        $this->Schedule = new Schedule($option);
        $this->Participant = new Participant($option);
        $this->ProgramSetting = new ProgramSetting($option);

        $this->Maker = new ScriptMaker();
    }


    public function tearDown()
    {
        $this->Dialogue->deleteAll(true, false);
        $this->Schedule->deleteAll(true, false);
        $this->Participant->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
        unset($this->Dialogue);
        unset($this->Schedule);
        parent::tearDown();
    }


    public function testSaveDialogue()
    {
        $dialogue = $this->Maker->getOneDialogue();
    
        $saveDraftFirstVersion = $this->Dialogue->saveDialogue($dialogue['Dialogue']);
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
        unset($dialogue['dialogue-id']);
        $saveDraftOtherDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->assertEquals(1, count($this->Dialogue->getActiveDialogues()));
        $this->assertEquals(2, count($this->Dialogue->getDialogues()));
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertEquals(2, count($activeAndDraft));
        $this->assertTrue($activeAndDraft[0]['Active']!=0);
        $this->assertTrue($activeAndDraft[0]['Draft']==0);
        $this->assertTrue($activeAndDraft[1]['Active']==0);
        $this->assertTrue($activeAndDraft[1]['Draft']!=0);
        $this->assertTrue(isset($activeAndDraft[1]['Draft']['interactions'][0]['activated']));

        //active the new Dialogue
        $saveActiveOtherDialogue = $this->Dialogue->makeDraftActive($saveDraftOtherDialogue['Dialogue']['dialogue-id']);
        $this->assertEquals(2, count($this->Dialogue->getActiveDialogues()));
        $this->assertEquals(2, count($this->Dialogue->getDialogues()));
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertTrue($activeAndDraft[0]['Active']!=0);
        $this->assertTrue($activeAndDraft[0]['Draft']==0);
        $this->assertTrue($activeAndDraft[1]['Active']!=0);
        $this->assertTrue($activeAndDraft[1]['Active']['interactions'][0]['activated']==1);
        $this->assertTrue($activeAndDraft[1]['Draft']==0);

        //add new version of the dialogue and check we get the correct one
        $dialogue = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['dialogue-id'] = $saveActiveOtherDialogue['Dialogue']['dialogue-id']; 
        $dialogue['Dialogue']['interactions'][0]['content'] = "something new";
        $saveNewVersionOtherDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeDraftActive($saveNewVersionOtherDialogue['Dialogue']['dialogue-id']);

        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertEquals("something new", $activeAndDraft[1]['Active']['interactions'][0]['content']);
        $this->assertEquals($saveNewVersionOtherDialogue['Dialogue']['_id'], $activeAndDraft[1]['Active']['_id']."");
        // check that older dialogue have activated switch to 2
        $saveActiveOtherDialogue = $this->Dialogue->read(null, $saveActiveOtherDialogue['Dialogue']['_id']);
        $this->assertEqual(2, $saveActiveOtherDialogue['Dialogue']['activated']);        

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
        $dialogue = $this->Maker->getOneDialogue();

        $saveResult = $this->Dialogue->saveDialogue($dialogue);
        //print_r($saveResult);
        $this->assertTrue(!empty($saveResult) && is_array($saveResult));
    
        $result = $this->Dialogue->find('all');
        $this->assertEqual(1, count($result));
        $this->assertEqual($result[0]['Dialogue']['interactions'][0]['date-time'], '2013-10-20T20:20:00');
    }


    public function testValidate_date_fail()
    {
        $dialogue = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['interactions'][0]['date-time'] = '2013-10-20 20:20:00';
        $saveResult = $this->Dialogue->saveDialogue($dialogue);
        $this->assertFalse(!empty($saveResult) && is_array($saveResult));    
    }

    public function testValidate_keyword_fail()
    {
        $dialogue = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['interactions'][0]['keyword'] = 'test, keyword 1, other';
        $saveResult = $this->Dialogue->saveDialogue($dialogue);
        $this->assertFalse($saveResult);
        $this->assertEqual(
            "The keyword/alias 'test, keyword 1, other' is not valid.",
            $this->Dialogue->validationErrors['dialogue']);
    }

    public function testFindAllKeywordInDialogues()
    {

        $dialogueOne = $this->Maker->getOneDialogue();
        $dialogueOne['Dialogue']['interactions'][0]['keyword'] = 'FEEL, Name';

        $dialogueTwo = $this->Maker->getOneDialogue();
        $dialogueTwo['Dialogue']['interactions'][0]['keyword'] = 'FEL';
      
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

    
    public function testDeleteDialogue()
    {
         $dialogueOne['Dialogue'] = array(
             'name'=> 'mydialgoue',
             'auto-enrollment' => 'none',
             'dialogue-id'=> '01'
             );
         $schedule['Schedule'] = array(
             'dialogue-id'=>'01',
             'interaction-id'=>'01',
             );   
         $dialogueTwo['Dialogue'] = array(
             'name'=> 'mydialgoue',
             'auto-enrollment' => 'none',
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
