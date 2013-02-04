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
            'fixed-time'=> $date->format('d/m/Y H:i')
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);

        $this->assertEquals(1, $this->UnattachedMessage->find('count'));
        $this->assertEquals('3', $savedUnattachedMessage['UnattachedMessage']['model-version']);
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
            'fixed-time'=> $date->format('d/m/Y H:i')
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);
        //$this->assertTrue($savedUnattachedMessage);
        $this->assertEquals(1, $this->UnattachedMessage->find('count'));
        $this->assertEquals('3', $savedUnattachedMessage['UnattachedMessage']['model-version']);
        $this->assertEquals('unattached-message', $savedUnattachedMessage['UnattachedMessage']['object-type']);
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
            'fixed-time'=> $date->format('d/m/Y H:i')
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
            'fixed-time'=> $date->format('d/m/Y H:i')
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
            'fixed-time'=> $date->format('d/m/Y H:i')
            );
        $this->UnattachedMessage->create("unattached-message");
        $this->assertFalse($this->UnattachedMessage->save($unattachedMessage));
        $this->assertEquals(0, $this->UnattachedMessage->find('count'));
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
            'fixed-time'=> $date->format('d/m/Y H:i')
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
            'fixed-time'=>'05/04/2012 14:30'
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
            'content'=>'hello there');
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
            'type-schedule' => 'fixed-time');
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
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage1 = $this->UnattachedMessage->save($unattachedMessage);
        
        $unattachedMessage = array(
            'name'=>'hello2',
            'send-to-type'=> 'all',
            'content'=>'hello there',
            'type-schedule'=>'immediately',
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage2 = $this->UnattachedMessage->save($unattachedMessage);
      
        $output = $this->UnattachedMessage->getNameIdForFilter();        
        $this->assertEquals(
        	array($savedUnattachedMessage1['UnattachedMessage']['_id'] => 'hello',
        		$savedUnattachedMessage2['UnattachedMessage']['_id'] => 'hello2'),
        	$output);
    }


}
