<?php
/* History Test cases generated on: 2012-01-24 15:57:36 : 1327409856*/
App::uses('History', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('ProgramSetting', 'Model');


class HistoryTestCase extends CakeTestCase
{
    
    public $fixtures = array('app.program', 'app.user', 'app.programsUser');
    
    
    public function setUp()
    {
        parent::setUp();
        
        $options                 = array('database' => 'testdbprogram');
        $this->History           = new History($options);
        $this->UnattachedMessage = new UnattachedMessage($options);
         $this->ProgramSetting    = new ProgramSetting($options);
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
        $this->UnattachedMessage->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
    }
    
    /*
    public function testFindScriptFilter()
    {
        $participantsState = array(
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-direction' => 'incoming',
            'interaction-id'=>'script.dialogues[0].interactions[0]',
            'dialogue-id'=>'script.dialogues[0]'
            );
        
        $this->History->create('dialogue-history');
        $history = $this->History->save($participantsState);
        
        
        $result   = $this->History->find('scriptFilter');
        $this->assertEquals(1, count($result));
    }
    
    
    public function testFindParticipant()
    {   
        $participantsState = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-direction' => 'incoming',
            'interaction-id'=>'script.dialogues[0].interactions[0]',
            'dialogue-id'=>'script.dialogues[0]'
            );
        
        $this->History->create('dialogue-history');
        $history = $this->History->save($participantsState);
        
        $result   = $this->History->find('participant',array(
            'phone'=>$participantsState['participant-phone']
            ));
        $this->assertEquals(1, count($result));
    }
    
    
    public function testFindCount()
    {
        $participantsState = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL Good',
            'message-direction' => 'incoming' 
            );
        
        $this->History->create('dialogue-history');
        $history = $this->History->save($participantsState);
        
        $result   = $this->History->find(
            'count'
            );
        $this->assertEquals(1, $result);
    }
    
    
    public function testCountFiltered()
    {
        $participantsState = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-direction' => 'incoming',
            'dialogue-id'=>'script.dialogues[0]',
            'interaction-id'=>'script.dialogues[0].interactions[0]'
            );
        
        $this->History->create('dialogue-history');
        $history = $this->History->save($participantsState);
        
        $state = 'before';
        
        $result = $this->History->find('count', array('type' => 'scriptFilter'));
        $this->assertEquals(1, $result);    
    }
    
    

    public function testFromFilterToQueryConditions_messageDirection()
    {
        $filterParam = array(
            1 => 'message-direction', 
            2 => 'is', 
            3 => 'incoming');

        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('message-direction' => 'incoming'));
        
        $filterParam = array(
            1 => 'message-direction', 
            2 => 'not-is', 
            3 => 'incoming'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('message-direction' => array('$ne' => 'incoming')));
    }
   
    
    public function testfromFilterToQueryCondition_messageStatus()
    {
        $filterParam = array(
            1 => 'message-status', 
            2 => 'is', 
            3 => 'pending'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('message-status' => 'pending'));
        
        $filterParam = array(
            1 => 'message-status', 
            2 => 'not-is', 
            3 => 'pending'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('message-status' => array('$ne' => 'pending')));
    }
    
    

    public function testFromFilterToQueryConditions_date()
    {
        $filterParam = array(
            1 => 'date', 
            2 => 'from', 
            3 => '21/01/2012'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('timestamp' => array('$gt' => '2012-01-21T00:00:00')));
        
        $filterParam = array(
            1 => 'date', 
            2 => 'to', 
            3 => '21/01/2012'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('timestamp' => array('$lt' => '2012-01-21T00:00:00')));
    }
    
    
    public function testfromFilterToQueryCondition_participantPhone()
    {
        $filterParam = array(
            1 => 'participant-phone', 
            2 => 'start-with', 
            3 => '+255'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('participant-phone' => new MongoRegex('/^\\+255/')));
        
        $filterParam = array(
            1 => 'participant-phone', 
            2 => 'equal-to', 
            3 => '+255'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('participant-phone' => '+255'));
        
        $filterParam = array(
            1 => 'participant-phone', 
            2 => 'start-with-any', 
            3 => '+255, +256'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('$or' => array(
                array('participant-phone' => new MongoRegex('/^\\+255/')),
                array('participant-phone' => new MongoRegex('/^\\+256/')))));
    }

    
    public function testfromFilterToQueryCondition_messageContent()
    {
        $filterParam = array(
            1 => 'message-content', 
            2 => 'equal-to', 
            3 => 'content'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('message-content' => 'content'));
        
        $filterParam = array(
            1 => 'message-content', 
            2 => 'contain', 
            3 => 'content'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('message-content' => new MongoRegex('/content/i')));
        
        $filterParam = array(
            1 => 'message-content', 
            2 => 'has-keyword', 
            3 => 'keyword'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('message-content' => new MongoRegex('/^keyword($| )/i')));
        
        $filterParam = array(
            1 => 'message-content', 
            2 => 'has-keyword-any', 
            3 => 'keyword1,keyword2'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array(
                '$or' => array(
                    array('message-content' => new MongoRegex('/^keyword1($| )/i')),
                    array('message-content' => new MongoRegex('/^keyword2($| )/i'))
                    ))
            );
    }
    
    
    public function testfromFilterToQueryCondition_dialogueSource()
    {
        $filterParam = array(
            1 => 'dialogue-source', 
            2 => 'is', 
            3 => '1'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('dialogue-id' => '1'));

        $filterParam = array(
            1 => 'dialogue-source', 
            2 => 'not-is', 
            3 => '1'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('dialogue-id' => array('$ne' => '1')));

        $filterParam = array(
            1 => 'dialogue-source', 
            2 => 'is-any');
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('dialogue-id' => array('$exists' => true)));

        $filterParam = array(
            1 => 'dialogue-source', 
            2 => 'not-is-any'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('dialogue-id' => array('$exists' => false)));
    }

    
    public function testfromFilterToQueryCondition_interactionSource()
    {
        $filterParam = array(
            1 => 'interaction-source', 
            2 => 'is', 
            3 => '1'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('interaction-id' => '1'));

        $filterParam = array(
            1 => 'interaction-source', 
            2 => 'not-is', 
            3 => '1'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('interaction-id' => array('$ne' => '1')));

        $filterParam = array(
            1 => 'interaction-source', 
            2 => 'is-any'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('interaction-id' => array('$exists' => true)));

        $filterParam = array(
            1 => 'interaction-source', 
            2 => 'not-is-any'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('interaction-id' => array('$exists' => false)));
    }


    public function testfromFilterToQueryCondition_requestSource()
    {
        $filterParam = array(
            1 => 'request-source', 
            2 => 'is', 
            3 => '52cd91759fa4da0051000004'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('request-id' => new MongoId('52cd91759fa4da0051000004')));

        $filterParam = array(
            1 => 'request-source', 
            2 => 'not-is', 
            3 => '52cd91759fa4da0051000004'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('request-id' => array('$ne' => new MongoId('52cd91759fa4da0051000004'))));

        $filterParam = array(
            1 => 'request-source', 
            2 => 'is-any');
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('request-id' => array('$exists' => true)));
        
        $filterParam = array(
            1 => 'request-source', 
            2 => 'not-is-any'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array('request-id' => array('$exists' => false)));
    }
    
  
    public function testfromFilterToQueryCondition_answer()
    {
        $filterParam = array(
            1 => 'answer', 
            2 => 'matching'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array(
                'message-direction' => 'incoming',
                'matching-answer' => array('$ne' => null))
            );
        
        $filterParam = array(
            1 => 'answer', 
            2 => 'not-matching'); 
        $this->assertEqual(
            $this->History->fromFilterToQueryCondition($filterParam),
            array(
                'message-direction' => 'incoming',
                'matching-answer' => null)
            );
    }

    
    public function testStatusOfUnattachedMessage()
    {       
        
        $history = array(
            'object-type' => 'unattach-history',
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-direction' => 'outgoing', 
            'message-status' => 'pending',
            'unattach-id' =>'5'
            );        
        
        $this->History->create('unattach-history');
        $saveHistoryStatus = $this->History->save($history); 
        
        
        $history_01 = array(
            'object-type' => 'datepassed-marker-history',
            'participant-phone' => '7886014620',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-direction' => 'outgoing',
            'unattach-id' =>'9'
            );        
        
        $this->History->create('unattach-history');
        $saveHistoryStatus = $this->History->save($history_01);     
        
        $output = $this->History->countUnattachedMessages('5');       
        $this->assertEquals(1, $output);   
        
        $output = $this->History->countUnattachedMessages('5','pending');       
        $this->assertEquals(1, $output); 
        
        $output = $this->History->countUnattachedMessages('5','delivered');       
        $this->assertEquals(0, $output);
        
        $output = $this->History->countUnattachedMessages('9');       
        $this->assertEquals(1, $output);  
        
        $output = $this->History->countUnattachedMessages('9', 'datepassed-marker');       
        $this->assertEquals(1, $output);  
    } 
    */
    
    public function testDatepassed_Marker_History_on_UnattachedMessage()
    {       
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $unattachedMessage = array(
            'name'=>'hello2',
            'send-to-type'=> 'all',
            'content'=>'hello there',
            'type-schedule'=>'immediately',
            'created-by' => 1,
            '_id' => 04
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);
        
        $history_01 = array(
            'object-type' => 'datepassed-marker-history',
            'participant-phone' => '7886014620',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-direction' => 'outgoing',
            'unattach-id' =>'04'
            );        
        $this->History->create('unattach-history');
        $saveHistoryStatus = $this->History->save($history_01);
        
        
        $output = $this->History->getParticipantHistory('7886014620', '');
        
       
    } 
    
}
