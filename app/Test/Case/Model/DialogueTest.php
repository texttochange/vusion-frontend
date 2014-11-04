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
        
        $option               = array('database'=>'testdbprogram');
        $this->Dialogue       = new Dialogue($option);
        $this->Schedule       = new Schedule($option);
        $this->Participant    = new Participant($option);
        $this->ProgramSetting = new ProgramSetting($option);
        
        $this->Maker = new ScriptMaker();
        $this->dropData();
    }

    public function dropData()
    {
        $this->Dialogue->deleteAll(true, false);
        $this->Schedule->deleteAll(true, false);
        $this->Participant->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
    }


    public function tearDown()
    {
        $this->dropData();
        unset($this->Dialogue);
        unset($this->Schedule);
        unset($this->Participant);
        unset($this->ProgramSetting);
        parent::tearDown();
    }
    
    
    public function testSaveDialogue()
    {
        $dialogue = $this->Maker->getOneDialogue();
        
        $saveDraftFirstVersion = $this->Dialogue->saveDialogue($dialogue['Dialogue']);
        $this->assertEquals(0, $saveDraftFirstVersion['Dialogue']['activated']);
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>0))));
        
        $saveActiveFirstVersion = $this->Dialogue->makeDraftActive($saveDraftFirstVersion['Dialogue']['dialogue-id']);
        $this->assertEquals(1, $saveActiveFirstVersion['Dialogue']['activated']);
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>1))));
        
        $this->Dialogue->saveDialogue($saveActiveFirstVersion);
        $this->assertEquals(2, $this->Dialogue->find('count'));
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>0)))); 
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>1)))); 
        
        $this->Dialogue->saveDialogue($saveActiveFirstVersion);
        $this->assertEquals(2, $this->Dialogue->find('count'));
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>0))));
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>1)))); 
        
        $saveActiveSecondVersion = $this->Dialogue->makeDraftActive($saveDraftFirstVersion['Dialogue']['dialogue-id']);
        $this->assertEquals(1, count($this->Dialogue->getActiveDialogues()));
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>1))));
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>2))));        
        
        
        //adding a new Dialogue
        unset($dialogue['dialogue-id']);
        $dialogue['Dialogue']['name'] = 'tom2';
        $saveDraftOtherDialogue       = $this->Dialogue->saveDialogue($dialogue);
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>0))));
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>1))));
        $this->assertEquals(1, $this->Dialogue->find('count', array('conditions' => array('activated'=>2))));        
        
        $this->assertEquals(1, count($this->Dialogue->getActiveDialogues()));
        //$this->assertEquals(2, count($this->Dialogue->getDialogues()));
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
        //$this->assertEquals(2, count($this->Dialogue->getDialogues()));
        $activeAndDraft = $this->Dialogue->getActiveAndDraft();
        $this->assertTrue($activeAndDraft[0]['Active']!=0);
        $this->assertTrue($activeAndDraft[0]['Draft']==0);
        $this->assertTrue($activeAndDraft[1]['Active']!=0);
        $this->assertTrue($activeAndDraft[1]['Active']['interactions'][0]['activated']==1);
        $this->assertTrue($activeAndDraft[1]['Draft']==0);
        
        //add new version of the dialogue and check we get the correct one
        $dialogue81                                           = $this->Maker->getOneDialogue();
        $dialogue81['Dialogue']['dialogue-id']                = $saveActiveOtherDialogue['Dialogue']['dialogue-id']; 
        $dialogue81['Dialogue']['interactions'][0]['content'] = "something new";
        $dialogue81['Dialogue']['name']                       = 'tom4';
        $saveNewVersionOtherDialogue                          = $this->Dialogue->saveDialogue($dialogue81);
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
        $schedule      = $this->Maker->getDialogueSchedule(
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
        $dialogue   = $this->Maker->getOneDialogue();
        $saveResult = $this->Dialogue->saveDialogue($dialogue);
        $this->assertTrue(!empty($saveResult) && is_array($saveResult));
        
        $result = $this->Dialogue->find('all');
        $this->assertEqual(1, count($result));
        $this->assertEqual($result[0]['Dialogue']['interactions'][0]['date-time'], '2013-10-20T20:20:00');
    }
    
    
    public function testValidate_date_fail()
    {
        $dialogue                                             = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['interactions'][0]['date-time'] = '2013-10-20 20:20:00';
        $saveResult                                           = $this->Dialogue->saveDialogue($dialogue);
        
        $this->assertFalse(!empty($saveResult) && is_array($saveResult));    
    }
    
    public function testValidate_keyword_fail_regex()
    {
        $dialogue                                           = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['interactions'][0]['keyword'] = 'test, keyword 1, other';
        
        $this->assertFalse($this->Dialogue->saveDialogue($dialogue));
        $this->assertEqual(
            "The keyword/alias is(are) not valid.",
            $this->Dialogue->validationErrors['interactions'][0]['keyword'][0]);
    }
    
    public function testValidate_keyword_fail_usedKeyword()
    {
        $usedKeywords = array(
            'other' => array(
                'program-db' => 'otherprogram',
                'program-name' => 'other program',
                'by-type' => 'Request'));
        $dialogue                                           = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['interactions'][0]['keyword'] = 'test, other';
        
        $this->assertFalse($this->Dialogue->saveDialogue($dialogue, $usedKeywords));
        $this->assertEqual(
            "'other' already used by a Request of program 'other program'.",
            $this->Dialogue->validationErrors['interactions'][0]['keyword'][0]);
    }
    
    public function testValidate_keyword_ok()
    {
        $dialogue                                           = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['interactions'][0]['keyword'] = 'frère, brother';
        $savedDialogue                                      = $this->Dialogue->saveDialogue($dialogue);
        
        $this->assertTrue(isset($savedDialogue));
    }
    

    public function testValidate_autoenrollment_condition_failed()
    {
        $dialogue                                = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['auto-enrollment'] = 'matching-tag-lable';
        $savedDialogue                           = $this->Dialogue->saveDialogue($dialogue);
        
        $this->assertFalse($savedDialogue);
        $this->assertEquals(
            $this->Dialogue->validationErrors['auto-enrollment'][0],
            'The auto-enrollment value is not valid.');
    }

    public function testValidate_autoenrollment_condition_ok()
    {
        $dialogue                                   = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['auto-enrollment']    = 'match';
        $dialogue['Dialogue']['condition-operator'] = 'all-subconditions';
        $dialogue['Dialogue']['subconditions']      = array(
            array('subcondition-field' => 'tagged',
                'subcondition-operator' => 'with',
                'subcondition-parameter' => 'geek'));
        $savedDialogue                              = $this->Dialogue->saveDialogue($dialogue);

        $this->assertTrue(isset($savedDialogue['Dialogue']));
    }
   
    public function testValidate_autoenrollment_condition_fail()
    {
        $dialogue                                = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['auto-enrollment'] = 'match';
        $dialogue['Dialogue']['subconditions']   = array(
            array('subcondition-field' => 'tagged',
                'subcondition-operator' => '',
                'subcondition-parameter' => 'geek'));
        $savedDialogue                           = $this->Dialogue->saveDialogue($dialogue);

       $this->assertFalse($savedDialogue);
        $this->assertEquals(
            $this->Dialogue->validationErrors['condition-operator'][0],
            'An operator between conditions has to be selected.');
        $this->assertEqual(
            $this->Dialogue->validationErrors['subconditions'][0]['subcondition-operator'][0],
            'The operator value \'\' is not valid.');
    }

    public function testValidate_autoenrollment_condition_parameter()
    {
        $dialogue                                   = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['auto-enrollment']    = 'match';
        $dialogue['Dialogue']['condition-operator'] = 'all-subconditions';
        $dialogue['Dialogue']['subconditions'] = array(
            array('subcondition-field' => 'tagged',
                'subcondition-operator' => 'with',
                'subcondition-parameter' => 'ugandan'),
            array('subcondition-field' => 'tagged',
                'subcondition-operator' => 'undefined',
                'subcondition-parameter' => 'ge:k'));
        $savedDialogue                              = $this->Dialogue->saveDialogue($dialogue);
        
        $this->assertFalse($savedDialogue);
        $this->assertEqual(
            $this->Dialogue->validationErrors['subconditions'][1]['subcondition-operator'][0],
            'The operator value \'undefined\' is not valid.');
    }

    
    public function testUseKeyword()
    {
        
        $dialogueOne                     = $this->Maker->getOneDialogue('FEEL, Name');
        $dialogueTwo                     = $this->Maker->getOneDialogue('FEL');
        $dialogueTwo['Dialogue']['name'] = 'other name';
        
        $savedDialogueOne = $this->Dialogue->saveDialogue($dialogueOne);
        $this->Dialogue->makeDraftActive($savedDialogueOne['Dialogue']['dialogue-id']);
        
        $savedDialogueTwo = $this->Dialogue->saveDialogue($dialogueTwo);
        $this->Dialogue->makeDraftActive($savedDialogueTwo['Dialogue']['dialogue-id']);    
        
        $this->assertEquals(
            array(
                'feel' => array(
                    'dialogue-id' => $savedDialogueOne['Dialogue']['dialogue-id'],
                    'dialogue-name' => $savedDialogueOne['Dialogue']['name']), 
                'fel' => array(
                    'dialogue-id' => $savedDialogueTwo['Dialogue']['dialogue-id'],
                    'dialogue-name' => $savedDialogueTwo['Dialogue']['name'])), 
            $this->Dialogue->useKeyword('FEEL, Fel'));
        
        $this->assertEquals(
            array('fel' => array(
                'dialogue-id' => $savedDialogueTwo['Dialogue']['dialogue-id'],
                'dialogue-name' => $savedDialogueTwo['Dialogue']['name'])), 
            $this->Dialogue->useKeyword('FEEL, Fel', $savedDialogueOne['Dialogue']['dialogue-id']));
        
        $this->assertEquals(
            array('name' => array(
                'dialogue-id' => $savedDialogueOne['Dialogue']['dialogue-id'],
                'dialogue-name' => $savedDialogueOne['Dialogue']['name'])),
            $this->Dialogue->useKeyword('NAME'));
        
        $this->assertEquals(
            false,
            $this->Dialogue->useKeyword('BT'));      
    }
    
    
    public function testSaveAddingId()
    {
        $dialogueOne['Dialogue'] = array(
            'auto-enrollment' => 'none',
            'interactions' => array(
                array(
                    'type-schedule' => 'fixed-time',
                    'date-time' => '2012-10-10T10:10:10',
                    'type-interaction' => 'question-answer', 
                    'type-question' => 'open-question',
                    'content' => 'how are you', 
                    'keyword' => 'FEEL', 
                    ),
                array(
                    'type-schedule' => 'fixed-time',
                    'date-time' => '2012-10-10T10:10:10',
                    'type-interaction' => 'question-answer',
                    'type-question' => 'open-question', 
                    'content' => 'How old are you?', 
                    'keyword' => 'Age', 
                    ),
                array( 
                    'type-schedule' => 'fixed-time',
                    'date-time' => '2012-10-10T10:10:10',
                    'type-interaction' => 'question-answer',
                    'type-question' => 'open-question',
                    'content' => 'What is you name?', 
                    'keyword'=> 'NAME', 
                    'interaction-id' => 'id not to be removed'
                    )
                )
            );
        
        
        $saveDialogue = $this->Dialogue->saveDialogue($dialogueOne);
        $this->assertTrue(isset($saveDialogue['Dialogue']['dialogue-id']));
        $this->assertTrue(isset($saveDialogue['Dialogue']['interactions'][0]['interaction-id']));
        $this->assertNotEqual(
            $saveDialogue['Dialogue']['interactions'][0]['interaction-id'],
            $saveDialogue['Dialogue']['interactions'][1]['interaction-id']);
        $this->assertEquals(
            'id not to be removed', 
            $saveDialogue['Dialogue']['interactions'][2]['interaction-id']);      
    }
    
    public function testSaveConditionalScheduleReplacingLocalId()
    {
        $dialogueOne['Dialogue'] = array(
            'auto-enrollment' => 'none',
            'interactions' => array(
                array(
                    'type-schedule' => 'fixed-time',
                    'date-time' => '2012-10-10T10:10:10',
                    'type-interaction' => 'question-answer', 
                    'type-question' => 'open-question',
                    'interaction-id' => 'local:48',
                    'content' => 'how are you', 
                    'keyword' => 'FEEL', 
                    ),
                array(
                    'type-schedule' => 'offset-condition',
                    'offset-condition-interaction-id' => 'local:48',
                    'type-interaction' => 'question-answer',
                    'interaction-id' => 'local:50',
                    'type-question' => 'open-question',
                    'content' => 'How old are you?', 
                    'keyword' => 'Age', 
                    ),
                 array( 
                    'type-schedule' => 'fixed-time',
                    'date-time' => '2012-10-10T10:10:10',
                    'type-interaction' => 'question-answer',
                    'type-question' => 'open-question',
                    'content' => 'What is you name?', 
                    'keyword'=> 'NAME', 
                    'interaction-id' => '74363622826'
                    )
                )
            );
        
        
        $savedDialogue = $this->Dialogue->saveDialogue($dialogueOne);
        $this->assertTrue(isset($savedDialogue['Dialogue']['interactions'][0]['interaction-id']));
        $this->assertNotEqual(
            'local:48',
            $savedDialogue['Dialogue']['interactions'][0]['interaction-id']);
        $this->assertEqual(
            $savedDialogue['Dialogue']['interactions'][0]['interaction-id'],
            $savedDialogue['Dialogue']['interactions'][1]['offset-condition-interaction-id']);
        $this->assertNotEqual(
            'local:50',
            $savedDialogue['Dialogue']['interactions'][1]['interaction-id']);
        $this->assertNotEqual(
            '74363622826',
            $savedDialogue['Dialogue']['interactions'][1]['interaction-id']);
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
            'name'=> 'mydialgoue2',
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
    
    
    public function testBeforeValidate()
    {
        $dialogue = $this->Maker->getOneDialogue();
        
        $this->assertFalse(array_key_exists('set-prioritized', $dialogue['Dialogue']));
        
        $dialog = $this->Dialogue->saveDialogue($dialogue);
        
        $this->assertEqual($this->Dialogue->getModelVersion(), $dialog['Dialogue']['model-version']);
        $this->assertTrue(array_key_exists('set-prioritized', $dialog['Dialogue']));
        $this->assertEqual($dialog['Dialogue']['set-prioritized'], null);
    }
    
    
    public function testSaveDialogue_interactionValidation_fail()
    {
        $dialogue = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['interactions'][1] = array(
            'type-schedule' => 'fixed-time',
            'date-time' => '02/03/2013 20:20',
            'type-interaction' => 'annoucement', 
            'content' => 'hello',
            'keyword' => 'greet',
            );
        
        $this->assertFalse($this->Dialogue->saveDialogue($dialogue));
        $this->assertEqual(
            'Type Interaction value is not valid.',
            $this->Dialogue->validationErrors['interactions'][1]['type-interaction'][0]
            );
    }
    
    public function testSaveDialogue_beforeValidate_prioritized()
    {
        $dialogue                                = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['interactions'][1] = array(
            'type-schedule' => 'fixed-time',
            'date-time' => '02/03/2013 20:20',
            'type-interaction' => 'announcement', 
            'content' => 'hello',
            );
        $dialogue['Dialogue']['set-prioritized'] = 'prioritized';
        $dialog                                  = $this->Dialogue->saveDialogue($dialogue);
        
        $this->assertEqual($dialog['Dialogue']['interactions'][0]['prioritized'], 'prioritized');
        $this->assertEqual($dialog['Dialogue']['interactions'][1]['prioritized'], 'prioritized');
        
        $dialogue2 = $this->Maker->getOneDialogue();
        $dialog2   = $this->Dialogue->saveDialogue($dialogue2);
        
        $this->assertEqual($dialog2['Dialogue']['interactions'][0]['prioritized'], null);
        
        $dialogue3                                = $this->Maker->getOneDialogue();
        $dialogue3['Dialogue']['interactions'][1] = array(
            'type-schedule' => 'fixed-time',
            'date-time' => '02/03/2013 20:20',
            'type-interaction' => 'announcement', 
            'content' => 'hello',
            'prioritized' => 'prioritized'
            );
        $dialogue3['Dialogue']['interactions'][0] = array(
            'type-schedule' => 'fixed-time',
            'date-time' => '02/03/2013 20:20',
            'type-interaction' => 'announcement', 
            'content' => 'hello',
            'keyword' => 'greet',
            'prioritized' => ''
            );
        
        $this->assertEqual($dialogue3['Dialogue']['interactions'][0]['prioritized'], null);
        $this->assertEqual($dialogue3['Dialogue']['interactions'][1]['prioritized'], 'prioritized');
    }
    
    
    public function testSaveDialogue_prioritized_default()
    {
        $dialogue = $this->Maker->getOneDialogue();        
        $this->Dialogue->saveDialogue($dialogue);
        
        $savedDialogue = $this->Dialogue->find('first');
        $this->assertEqual($savedDialogue['Dialogue']['set-prioritized'], null);   
    }

    public function testSaveDialogue_prioritized() 
    {
        $dialogue = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['set-prioritized'] = 'prioritized';        
        $this->Dialogue->saveDialogue($dialogue);
        
        $savedDialogue = $this->Dialogue->find('first');
        $this->assertEqual($savedDialogue['Dialogue']['set-prioritized'], 'prioritized');
    }
    
    
    public function testUniqueDialogueName_dialogueIdSame_pass()
    {
    	$dialogue                             = $this->Maker->getOneDialogue();
    	$savedDialogueOne                     = $this->Dialogue->saveDialogue($dialogue);    	
    	
    	$dialogue2                            = $this->Maker->getOneDialogue();
    	$dialogue2['Dialogue']['dialogue-id'] = $savedDialogueOne['Dialogue']['dialogue-id'];
    	$savedDialogueTwo                     = $this->Dialogue->saveDialogue($dialogue2);
    	
    	$this->assertEquals($savedDialogueOne['Dialogue']['name'],$savedDialogueTwo['Dialogue']['name']); 
    	$this->assertTrue(isset($savedDialogueTwo));
    }
    
    public function testUniqueDialogueName_dialogueIdDifferent_fail()
    {
    	$dialogue         = $this->Maker->getOneDialogue();
    	$savedDialogueOne = $this->Dialogue->saveDialogue($dialogue);
    	
    	$dialogue2        = $this->Maker->getOneDialogue();
    	$savedDialogueTwo = $this->Dialogue->saveDialogue($dialogue2);
    	
    	$this->assertEquals(1,$this->Dialogue->find('count'));
    	$this->assertEquals($savedDialogueTwo['Dialogue'],null);
    	$this->assertEquals($this->Dialogue->validationErrors['name'][0], 'This Dialogue Name already exists. Please choose another.');
    	
    }
    
    
    public function testUniqueDialogueName_dialogueIdDifferent_pass()
    {
    	$dialogue         = $this->Maker->getOneDialogue();
    	$savedDialogueOne = $this->Dialogue->saveDialogue($dialogue);    	
    	
    	$dialogue2                     = $this->Maker->getOneDialogue();
    	$dialogue2['Dialogue']['name'] = 'tom';
    	$savedDialogueTwo              = $this->Dialogue->saveDialogue($dialogue2);
    	
    	$this->assertNotEqual($savedDialogueOne['Dialogue']['name'],$savedDialogueTwo['Dialogue']['name']);
    	$this->assertEqual(2,$this->Dialogue->find('count'));
    	$this->assertNotEqual($savedDialogueOne['Dialogue']['dialogue-id'],$savedDialogueTwo['Dialogue']['dialogue-id']);
    }
    
    
    public function testisValidDialogueName()
    {
    	$dialogue         = $this->Maker->getOneDialogue();
    	$savedDialogueOne = $this->Dialogue->saveDialogue($dialogue);
    	
    	$dialogue2                            = $this->Maker->getOneDialogue();
    	$dialogue2['Dialogue']['name']        = 'tom';
    	$dialogue2['Dialogue']['dialogue-id'] = '08';
    	$savedDialogueTwo                     = $this->Dialogue->saveDialogue($dialogue2);
    	
    	$output = $this->Dialogue->isValidDialogueName('tom','08');
    	$this->assertEqual(1,$output);
		$this->assertTrue($output);    	
    }
    
    
    public function testHasDialogueKeyword()
    {
        $dialogue = $this->Maker->getOneDialogue('kÉyword');
        $this->assertEquals(
            array('keyword'),
            Dialogue::hasDialogueKeywords($dialogue, array('keyword')));
        
        $dialogue = $this->Maker->getOneDialogueWithKeyword();
        $this->assertEquals(
            array('feel', 'keyword'),
            Dialogue::hasDialogueKeywords($dialogue, array('feel', 'keyword')));
        
        $dialogue = $this->Maker->getOneDialogueWithKeyword('keyword');
        $this->assertEquals(
            array('keyword'),
            Dialogue::hasDialogueKeywords($dialogue, array('keyword')));
    }
    
    
    public function testHasDialogueKeywork_answerAcceptNoSpace()
    {
        $dialogue = $this->Maker->getOneDialogueAnwerNoSpaceSupported('fÉl');
        $this->assertEquals(
            array('felgood'),
            Dialogue::hasDialogueKeywords($dialogue, array('felgood')));
        $this->assertEquals(
            array('fel'),
            Dialogue::hasDialogueKeywords($dialogue, array('fel')));
        $this->assertEquals(
            array('felbad'),
            Dialogue::hasDialogueKeywords($dialogue, array('felbad')));
        $this->assertEquals(
            array(),
            Dialogue::hasDialogueKeywords($dialogue, array('felok')));
    }
    
    
    public function testGetKeyworks()
    {
        $dialogue = $this->Maker->getOneDialogueAnwerNoSpaceSupported('fÉl');
        $this->assertEquals(
            array('feel', 'fel', 'felgood','felbad'), 
            Dialogue::getDialogueKeywords($dialogue));
        
        $dialogue = $this->Maker->getOneDialogue('usedKeyword');
        $this->assertEquals(
            array('usedkeyword'), 
            Dialogue::getDialogueKeywords($dialogue));
        
        $dialogue = $this->Maker->getOneDialogueMultikeyword();
        $this->assertEqual(
            array('female', 'male'),
            Dialogue::getDialogueKeywords($dialogue));
    }
    
    
}
