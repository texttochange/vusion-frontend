<?php

App::uses('UnattachedMessage', 'Model');
App::uses('ProgramSetting', 'Model');

class UnattachedMessageTestCase extends CakeTestCase
{
    
    
    public function setUp()
    {
        parent::setUp();

        $options                 = array('database' => 'testdbprogram');
        $this->UnattachedMessage = new UnattachedMessage($options);
        $this->ProgramSetting    = new ProgramSetting($options);
        
        $this->dropData();
        
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        
        unset($this->UnattachedMessage);
        
        parent::tearDown();
    }
    
    
    public function dropData()
    {
        $this->UnattachedMessage->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
    }


    public function testSave_ok_allParticipants()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'all',
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i'),
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);

        $this->assertEquals(1, $this->UnattachedMessage->find('count'));
        $this->assertEquals('4', $savedUnattachedMessage['UnattachedMessage']['model-version']);
        $this->assertEquals('unattached-message', $savedUnattachedMessage['UnattachedMessage']['object-type']);
    }


    public function testSave_ok_matchParticipants()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'match',
            'send-to-match-operator' => 'all',
            'send-to-match-conditions' => array('a tag'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i'),
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);
        //$this->assertTrue($savedUnattachedMessage);
        $this->assertEquals(1, $this->UnattachedMessage->find('count'));
        $this->assertEquals('4', $savedUnattachedMessage['UnattachedMessage']['model-version']);
        $this->assertEquals('unattached-message', $savedUnattachedMessage['UnattachedMessage']['object-type']);
    }
    
    
    public function testSave_fail_noCreatedBy()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'match',
            'send-to-match-operator' => 'all',
            'send-to-match-conditions' => array('a tag'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i')
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);
        
        $this->assertEquals(0, $this->UnattachedMessage->find('count'));
        $this->assertEquals(
            'Message must be created by a user.',
            $this->UnattachedMessage->validationErrors['created-by'][0]);
    }


    public function testSave_fail_matchParticipants_noSendToOperator()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'match',
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i'),
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->assertFalse($this->UnattachedMessage->save($unattachedMessage));
        $this->assertEquals(0, $this->UnattachedMessage->find('count'));
    }
    
    public function testSave_fail_matchParticipants_noSendToMatchConditions()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'match',
            'send-to-match-operator'=> 'all',
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i'),
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->assertFalse($this->UnattachedMessage->save($unattachedMessage));
        $this->assertEquals(
            'Select conditions.',
            $this->UnattachedMessage->validationErrors['send-to-match-conditions'][0]);   
    }


    public function testSave_fail_matchParticipants_noSendToType()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-match-operator'=> 'all',
            'send-to-match-conditions' => array('a tag'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i'),
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->assertFalse($this->UnattachedMessage->save($unattachedMessage));
        $this->assertEquals(0, $this->UnattachedMessage->find('count'));
    }


    public function testSave_fail_forbiddenapostrophe()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name' => 'hello',
            'send-to-type'=> 'all', 
            'content' => 'what’s that',
            'type-schedule' => 'fixed-time',
            'fixed-time' => $date->format('d/m/Y H:i')
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->assertFalse($this->UnattachedMessage->save($unattachedMessage));
        $this->assertEquals(
            'The apostrophe used in this message is not valid.',
            $this->UnattachedMessage->validationErrors['content'][0]);
    }


    public function testSave_ok_update_matchToPhone()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'match',
            'send-to-match-operator' => 'all',
            'send-to-match-conditions' => array('a tag'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i'),
            'created-by'=>1
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);
        $this->assertEquals('match', $savedUnattachedMessage['UnattachedMessage']['send-to-type']);

        $unattachedMessage['send-to-type'] = "phone";
        $unattachedMessage['send-to-phone'] = array("+256777");
        
        $this->UnattachedMessage->id =  $savedUnattachedMessage['UnattachedMessage']['_id']."";
        $this->UnattachedMessage->save($unattachedMessage);
        $updateUnattachedMessage = $this->UnattachedMessage->find('first');
        $this->assertEquals(1, $this->UnattachedMessage->find('count'));
        $this->assertEquals('phone', $updateUnattachedMessage['UnattachedMessage']['send-to-type']);
        $this->assertEquals(
            $unattachedMessage['send-to-phone'], 
            $updateUnattachedMessage['UnattachedMessage']['send-to-phone']);
        $this->assertTrue(!isset($updateUnattachedMessage['UnattachedMessage']['send-to-match-operator']));
        $this->assertTrue(!isset($updateUnattachedMessage['UnattachedMessage']['send-to-match-conditions']));
    }


    public function testSave_ok_tagAndLabel()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-type' => 'match',
            'send-to-match-operator' => 'all',
            'send-to-match-conditions' => array(
                'geek',
                'a tag',
                'city:kampala',
                'some label:some value'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i'),
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->UnattachedMessage->save($unattachedMessage);

        $this->assertEquals(1,$this->UnattachedMessage->find('count'));
    }
        

    public function testSave_fail_fixedTime_isPast()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');

        $otherUnattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'all',
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=>'05/04/2012 14:30',
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->UnattachedMessage->save($otherUnattachedMessage);
        
        //1st assertion, count does not increase, remains 1
        $this->assertEquals(0,$this->UnattachedMessage->find('count'));
        //2st assertion, error fixed time cannot be in the past
        $this->assertEquals(
            'Fixed time cannot be in the past.',
            $this->UnattachedMessage->validationErrors['fixed-time'][0]);
    }
    

    public function testSave_fail_noScheduleType()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');

        $otherUnattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'all',
            'content'=>'hello there',
            'created-by' => 1);
        $this->UnattachedMessage->create("unattached-message");
        $this->UnattachedMessage->save($otherUnattachedMessage);
        
        //1st assertion, count does not increase, remains 1
        $this->assertEquals(0,$this->UnattachedMessage->find('count'));
        //2st assertion, error fixed time cannot be in the past
        $this->assertEquals(
            'Please choose a type of schedule for this message.',
            $this->UnattachedMessage->validationErrors['type-schedule'][0]);
        $this->assertEqual(
            "",
            $this->UnattachedMessage->data['UnattachedMessage']['fixed-time']);
    }


    public function testSave_fail_noFixedTime()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');

        $otherUnattachedMessage = array(
            'name' => 'hello',
            'send-to-type' => 'all',
            'content' => 'hello there',
            'type-schedule' => 'fixed-time',
            'created-by' => 1);
        $this->UnattachedMessage->create("unattached-message");
        $this->UnattachedMessage->save($otherUnattachedMessage);
        
        //1st assertion, count does not increase, remains 1
        $this->assertEquals(0,$this->UnattachedMessage->find('count'));
        //2st assertion, error fixed time cannot be in the past
        $this->assertEquals(
            'Please enter a fixed time for this message.',
            $this->UnattachedMessage->validationErrors['fixed-time'][0]);
    }


    public function testSave_ok_scheduleImmediatly()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');

        $otherUnattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'all',
            'content'=>'hello there',
            'type-schedule'=>'immediately',
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage = $this->UnattachedMessage->save($otherUnattachedMessage);
        
        $this->assertEquals(1,$this->UnattachedMessage->find('count'));
        $this->assertEquals('immediately', $savedUnattachedMessage['UnattachedMessage']['type-schedule']);
    }


    public function testSave_fail_specialCharacters()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-type' => 'match',
            'send-to-match-operator' => 'all',
            'send-to-match-conditions' => array('a\nt"ag'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i')
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);
        
        $this->assertFalse($savedUnattachedMessage);
        $this->assertEquals(
            'Incorrect tag or label.',
            $this->UnattachedMessage->validationErrors['send-to-match-conditions'][0]);
    }

    
    public function testGetNameIdForFilter()
    {
    	$this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');

    	$output = $this->UnattachedMessage->getNameIdForFilter();        
        $this->assertEquals(
        	null,
        	$output);
    	
    	
        $unattachedMessage = array(
            'name'=>'hello',
            'send-to-type'=> 'all',
            'content'=>'hello there',
            'type-schedule'=>'immediately',
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage1 = $this->UnattachedMessage->save($unattachedMessage);
        
        $unattachedMessage = array(
            'name'=>'hello2',
            'send-to-type'=> 'all',
            'content'=>'hello there',
            'type-schedule'=>'immediately',
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage2 = $this->UnattachedMessage->save($unattachedMessage);
      
        $output = $this->UnattachedMessage->getNameIdForFilter();        
        $this->assertEquals(
        	array($savedUnattachedMessage1['UnattachedMessage']['_id'] => 'hello',
        		$savedUnattachedMessage2['UnattachedMessage']['_id'] => 'hello2'),
        	     $output);
    }


    public function testIsNotPast()
    {    
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
         
        $now = new DateTime('now', timezone_open('Africa/Kampala'));   
        $check = array('fixed-time'=> $now->modify('-30 minutes')->format("Y-m-d\TH:i:s"));
        $this->assertFalse($this->UnattachedMessage->isNotPast($check));
    }


    public function testSave_ok_dynamicContent()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name' => 'hello',
            'send-to-type'=> 'all', 
            'content' => 'There is a an [participant.name] here.',
            'type-schedule' => 'fixed-time',
            'fixed-time' => $date->format('d/m/Y H:i'),
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->UnattachedMessage->save($unattachedMessage);
        $this->assertEquals(1,$this->UnattachedMessage->find('count'));
        
        $unattachedMessage02 = array(
            'name' => 'weather',
            'send-to-type'=> 'all', 
            'content' => 'The weather today is [contentVariable.program.weather].',
            'type-schedule' => 'fixed-time',
            'fixed-time' => $date->format('d/m/Y H:i'),
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->UnattachedMessage->save($unattachedMessage02);
        $this->assertEquals(2,$this->UnattachedMessage->find('count'));
    }


    public function testSave_fail_dynamicContent()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name' => 'hello',
            'send-to-type'=> 'all', 
            'content' => 'There is a an [shoe.box] here.',
            'type-schedule' => 'fixed-time',
            'fixed-time' => $date->format('d/m/Y H:i'),
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->assertFalse($this->UnattachedMessage->save($unattachedMessage));
        $this->assertEquals(
            "To be used as dynamic content, 'shoe' can only be either 'participant' or 'contentVariable'.",
            $this->UnattachedMessage->validationErrors['content'][0]);
        
        $unattachedMessage['content'] = "Hello [participant.gender.name]";
        $this->assertFalse($this->UnattachedMessage->save($unattachedMessage));
        $this->assertEquals(
            "To be used in message, participant only accept one key.",
            $this->UnattachedMessage->validationErrors['content'][0]);
        
        $unattachedMessage['content'] = "Hello [contentVariable.kampala.pork.male]";
        $this->assertFalse($this->UnattachedMessage->save($unattachedMessage));
        $this->assertEquals(
            "To be used in message, contentVariable only accept max two keys.",
            $this->UnattachedMessage->validationErrors['content'][0]);
    }


}
