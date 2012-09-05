<?php
/* History Test cases generated on: 2012-01-24 15:57:36 : 1327409856*/
App::uses('History', 'Model');


class HistoryTestCase extends CakeTestCase
{

    public $fixtures = array('app.program', 'app.user', 'app.programsUser');


    public function setUp()
    {
        parent::setUp();

        $this->History = ClassRegistry::init('History');
        
        $options                 = array('database' => 'testdbprogram');
        $this->History = new History($options);
        
        $this->dropData();
    }


    public function tearDown()
    {
        $this->dropData();
        
        unset($this->History);

        parent::tearDown();
    }
    
    
    public function dropData()
    {
        $this->History->deleteAll(true, false);
    }

    
    public function testFindScriptFilter()
    {
        $participantsState = array(
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-status' => null,
            'message-direction' => 'incoming',
            'interaction-id'=>'script.dialogues[0].interactions[0]',
            'dialogue-id'=>'script.dialogues[0]'
            );
        
        $this->History->create();
        $history = $this->History->save($participantsState);
        
              
        $result   = $this->History->find('scriptFilter');
        $this->assertEquals(1, count($result));
    }
    
    
    public function testFindParticipant()
    {   
        $participantsState = array(
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-status' => null,
            'message-direction' => 'incoming',
            'interaction-id'=>'script.dialogues[0].interactions[0]',
            'dialogue-id'=>'script.dialogues[0]'
            );
        
        $this->History->create();
        $history = $this->History->save($participantsState);
        
        $result   = $this->History->find('participant',array(
            'phone'=>$participantsState['participant-phone']
            ));
        $this->assertEquals(1, count($result));
    }
    
    
    public function testFindCount()
    {
        $participantsState = array(
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL Good',
            'message-status' => null,
            'message-direction' => 'incoming' 
            );
        
        $this->History->create();
        $history = $this->History->save($participantsState);
              
        $result   = $this->History->find(
            'count'
            );
        $this->assertEquals(1, $result);
    }
    
    
    public function testCountFiltered()
    {
        $participantsState = array(
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-status' => null,
            'message-direction' => 'incoming',
            'dialogue-id'=>'script.dialogues[0]',
            'interaction-id'=>'script.dialogues[0].interactions[0]'
            );
           
        $this->History->create();
        $history = $this->History->save($participantsState);
        
        $state = 'before';
              
        $result = $this->History->find('count', array('type' => 'scriptFilter'));
        $this->assertEquals(1, $result);    
    }


}
