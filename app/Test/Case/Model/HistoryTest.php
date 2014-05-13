<?php
/* History Test cases generated on: 2012-01-24 15:57:36 : 1327409856*/
App::uses('History', 'Model');
App::uses('DialogueHelper', 'Lib');


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
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'message-direction', 
                    2 => 'is', 
                    3 => 'incoming'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('message-direction' => 'incoming')
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'message-direction', 
                    2 => 'not-is', 
                    3 => 'incoming'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('message-direction' => array('$ne' => 'incoming'))
            );
    }
    
    
    public function testFromFilterToQueryConditions_messageStatus()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'message-status', 
                    2 => 'is', 
                    3 => 'pending'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('message-status' => 'pending')
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'message-status', 
                    2 => 'not-is', 
                    3 => 'pending'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('message-status' => array('$ne' => 'pending'))
            );
    }
    
    
    public function testFromFilterToQueryConditions_date()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'date', 
                    2 => 'from', 
                    3 => '21/01/2012'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('timestamp' => array('$gt' => '2012-01-21T00:00:00'))
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'date', 
                    2 => 'to', 
                    3 => '21/01/2012'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('timestamp' => array('$lt' => '2012-01-21T00:00:00'))
            );
    }
    
    
    public function testFromFilterToQueryConditions_participantPhone()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'participant-phone', 
                    2 => 'start-with', 
                    3 => '+255'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('participant-phone' => new MongoRegex('/^\\+255/'))
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'participant-phone', 
                    2 => 'equal-to', 
                    3 => '+255'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('participant-phone' => '+255')
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'participant-phone', 
                    2 => 'start-with-any', 
                    3 => '+255, +256'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('$or' => array(
                array('participant-phone' => new MongoRegex('/^\\+255/')),
                array('participant-phone' => new MongoRegex('/^\\+256/'))
                ))
            );
    }
    
    
    public function testFromFilterToQueryConditions_messageContent_validationFail()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'message-content', 
                    2 => 'has-keyword-any', 
                    3 => 'keyword1 keyword2'),
                )
            );
        try {
            $this->History->fromFilterToQueryConditions($filter);
            $this->fail();
        } catch (FilterException $e) {
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail();
        }
    }
    
    
    public function testFromFilterToQueryConditions_messageContent()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'message-content', 
                    2 => 'equal-to', 
                    3 => 'content'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('message-content' => 'content')
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'message-content', 
                    2 => 'contain', 
                    3 => 'content'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('message-content' => new MongoRegex('/content/i'))
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'message-content', 
                    2 => 'has-keyword', 
                    3 => 'keyword'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('message-content' => new MongoRegex('/^keyword($| )/i'))
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'message-content', 
                    2 => 'has-keyword-any', 
                    3 => 'keyword1,keyword2'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array(
                '$or' => array(
                    array('message-content' => new MongoRegex('/^keyword1($| )/i')),
                    array('message-content' => new MongoRegex('/^keyword2($| )/i'))
                    ))
            );
    }
    
    
    public function testFromFilterToQueryConditions_dialogueSource()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'dialogue-source', 
                    2 => 'is', 
                    3 => '1'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('dialogue-id' => '1')
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'dialogue-source', 
                    2 => 'not-is', 
                    3 => '1'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('dialogue-id' => array('$ne' => '1'))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'dialogue-source', 
                    2 => 'is-any'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('dialogue-id' => array('$exists' => true))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'dialogue-source', 
                    2 => 'not-is-any'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('dialogue-id' => array('$exists' => false))
            );
    }
    
    public function testFromFilterToQueryConditions_interactionSource()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'interaction-source', 
                    2 => 'is', 
                    3 => '1'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('interaction-id' => '1')
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'interaction-source', 
                    2 => 'not-is', 
                    3 => '1'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('interaction-id' => array('$ne' => '1'))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'interaction-source', 
                    2 => 'is-any'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('interaction-id' => array('$exists' => true))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'interaction-source', 
                    2 => 'not-is-any'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('interaction-id' => array('$exists' => false))
            );
    }


    public function testFromFilterToQueryConditions_requestSource()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'request-source', 
                    2 => 'is', 
                    3 => '52cd91759fa4da0051000004'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('request-id' => new MongoId('52cd91759fa4da0051000004'))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'request-source', 
                    2 => 'not-is', 
                    3 => '52cd91759fa4da0051000004'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('request-id' => array('$ne' => new MongoId('52cd91759fa4da0051000004')))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'request-source', 
                    2 => 'is-any'),
                )
            ); 
        
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('request-id' => array('$exists' => true))
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'request-source', 
                    2 => 'not-is-any'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('request-id' => array('$exists' => false))
            );
    }
    
    
    public function testFromFilterToQueryConditions_answer()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'answer', 
                    2 => 'matching'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array(
                'message-direction' => 'incoming',
                'matching-answer' => array('$ne' => null))
            );
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'answer', 
                    2 => 'not-matching'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array(
                'message-direction' => 'incoming',
                'matching-answer' => null)
            );
    }
    
    
    public function testFromFilterToQueryConditions_AND()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'participant-phone', 
                    2 => 'equal-to', 
                    3 => '+255'),
                array(
                    1 => 'participant-phone', 
                    2 => 'equal-to', 
                    3 => '+256'),
                array(
                    1 => 'participant-phone', 
                    2 => 'equal-to', 
                    3 => '+257'),
                )); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('$and' => array(
                array('participant-phone' => '+255'),
                array('participant-phone' => '+256'),
                array('participant-phone' => '+257'),
                ))
            );       
    }
    
    public function testFromFilterToQueryConditions_OR()
    {
        $filter = array(
            'filter_operator' => 'any',
            'filter_param' => array(
                array(
                    1 => 'participant-phone', 
                    2 => 'equal-to', 
                    3 => '+255'),
                array(
                    1 => 'participant-phone', 
                    2 => 'equal-to', 
                    3 => '+256'),
                array(
                    1 => 'participant-phone', 
                    2 => 'equal-to', 
                    3 => '+257'),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filter),
            array('$or' => array(
                array('participant-phone' => '+255'),
                array('participant-phone' => '+256'),
                array('participant-phone' => '+257'),
                ))
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
        
        $output = $this->History->countUnattachedMessages('5');       
        $this->assertEquals(1, $output);   
        
        $output = $this->History->countUnattachedMessages('5','pending');       
        $this->assertEquals(1, $output); 
        
        $output = $this->History->countUnattachedMessages('5','delivered');       
        $this->assertEquals(0, $output); 
    }            
    
}
