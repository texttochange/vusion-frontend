<?php
/* ParticipantsState Test cases generated on: 2012-01-24 15:57:36 : 1327409856*/
App::uses('ParticipantsState', 'Model');
App::uses('Script', 'Model');


class ParticipantsStateTestCase extends CakeTestCase
{

    public $fixtures = array('app.program', 'app.user', 'app.programsUser');


    public function setUp()
    {
        parent::setUp();

        $this->ParticipantsState = ClassRegistry::init('ParticipantsState');
        
        $options                 = array('database' => 'testdbprogram');
        $this->ParticipantsState = new ParticipantsState($options);
        $this->Script = new Script($options);
        
        
        $this->dropData();
    }


    public function tearDown()
    {
        $this->dropData();
        
        unset($this->ParticipantsState);

        parent::tearDown();
    }
    
    
    public function dropData()
    {
        $this->ParticipantsState->deleteAll(true, false);
    }

    
    public function testFindScriptFilter()
    {
        $participantsState = array(
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-status' => null,
            'message-type' => 'received',
            'interaction-id'=>'script.dialogues[0].interactions[0]',
            'dialogue-id'=>'script.dialogues[0]'
            );
        
        $this->ParticipantsState->create();
        $history = $this->ParticipantsState->save($participantsState);
        
        $script = array(
            0 => array(
                'Script' => array(
                    'script' => array(
                        'dialogues' => array(
                            0 => array (
                                'interactions'=>array(
                                    0 => array(
                                        'type-interaction'=>'question-answer',
                                        'content'=>'how do you feel',
                                        'keyword'=>'FEEL',
                                        'type-question'=>'close-question',
                                        'answers'=> array(
                                            0 => array('choice'=>'Good'),
                                            1 => array('choice'=>'Bad')
                                              ),
                                        'interaction-id'=>'script.dialogues[0].interactions[0]'
                                           )
                                    ),
                                'dialogue-id'=>'script.dialogues[0]'
                                )
                            )
                        )
                    )
                )
            );  

        $this->Script->recursive = -1;
        $this->Script->create();
        $this->Script->save($script);
        $this->Script->makeDraftActive();
              
        $result   = $this->ParticipantsState->find('scriptFilter',
            array('script' => $script)
            );
        $this->assertEquals(1, count($result));
    }
    
    
    public function testFindParticipant()
    {   
        $participantsState = array(
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-status' => null,
            'message-type' => 'received',
            'interaction-id'=>'script.dialogues[0].interactions[0]',
            'dialogue-id'=>'script.dialogues[0]'
            );
        
        $this->ParticipantsState->create();
        $history = $this->ParticipantsState->save($participantsState);
        
        $result   = $this->ParticipantsState->find('participant',array(
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
            'message-type' => 'received' 
            );
        
        $this->ParticipantsState->create();
        $history = $this->ParticipantsState->save($participantsState);
              
        $result   = $this->ParticipantsState->find(
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
            'message-type' => 'received',
            'dialogue-id'=>'script.dialogues[0]',
            'interaction-id'=>'script.dialogues[0].interactions[0]'
            );
        
        $script = array(
            0 => array(
                'Script' => array(
                    'script' => array(
                        'dialogues' => array(
                            0 => array (
                                'interactions'=>array(
                                    0 => array(
                                        'type-interaction'=>'question-answer',
                                        'content'=>'how do you feel',
                                        'keyword'=>'FEEL',
                                        'type-question'=>'close-question',
                                        'answers'=> array(
                                            0 => array('choice'=>'Good'),
                                            1 => array('choice'=>'Bad')
                                              ),
                                        'interaction-id'=>'script.dialogues[0].interactions[0]'
                                           )
                                    ),
                                'dialogue-id'=>'script.dialogues[0]'
                                )
                            )
                        )
                    )
                )
            );  

        $this->Script->recursive = -1;
        $this->Script->create();
        $this->Script->save($script);
        $this->Script->makeDraftActive();
        
        $this->ParticipantsState->create();
        $history = $this->ParticipantsState->save($participantsState);
        
        $state = 'before';
              
        $result = $this->ParticipantsState->find('count', array('type' => 'scriptFilter', 'script' => $script));
        $this->assertEquals(1, $result);    
    }


}
