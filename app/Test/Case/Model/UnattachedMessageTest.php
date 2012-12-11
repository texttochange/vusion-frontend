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
    
    
    /** Test Methods */
    
    public function testSave_ok()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'to'=> array('all-participants'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i')
            );
        $this->UnattachedMessage->create();
        $this->UnattachedMessage->save($unattachedMessage);

        $this->assertEquals(1,$this->UnattachedMessage->find('count'));
    }

    public function testSave_okTagAndLabel()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'to'=> array('geek', 'a tag', 'city:kampala', 'some label:some value'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i')
            );
        $this->UnattachedMessage->create();
        $this->UnattachedMessage->save($unattachedMessage);

        $this->assertEquals(1,$this->UnattachedMessage->find('count'));
    }
        

    public function testSave_failIsPast()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');

        $otherUnattachedMessage = array(
            'name'=>'hello',
            'to'=> array('a tag'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=>'05/04/2012 14:30'
            );
        $this->UnattachedMessage->create();
        $this->UnattachedMessage->save($otherUnattachedMessage);
        
        //1st assertion, count does not increase, remains 1
        $this->assertEquals(0,$this->UnattachedMessage->find('count'));
        //2st assertion, error fixed time cannot be in the past
        $this->assertEquals('Fixed time cannot be in the past.',$this->UnattachedMessage->validationErrors['fixed-time'][0]);
    }


    public function testSave_failSpecialCharacters()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow'); 
        $date->modify("+4 hour");
        $unattachedMessage = array(
            'name'=>'hello',
            'to'=> array('a\nt"ag'),
            'content'=>'hello there',
            'type-schedule'=>'fixed-time',
            'fixed-time'=> $date->format('d/m/Y H:i')
            );
        $this->UnattachedMessage->create();
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);
        //print_r($savedUnattachedMessage);
        $this->assertFalse($savedUnattachedMessage);
        $this->assertEquals('Please select message recipient.',$this->UnattachedMessage->validationErrors['to'][0]);
    }
    
}
