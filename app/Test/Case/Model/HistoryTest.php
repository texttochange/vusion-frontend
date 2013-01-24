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
        $filterOperator = 'all';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "message-direction", 
                    2 => "is", 
                    3 => "incoming"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('message-direction' => 'incoming')
            );

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "message-direction", 
                    2 => "not-is", 
                    3 => "incoming"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('message-direction' => array('$ne' => 'incoming'))
            );
    }


    public function testFromFilterToQueryConditions_messageStatus()
    {
        $filterOperator = 'all';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "message-status", 
                    2 => "is", 
                    3 => "pending"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('message-status' => 'pending')
            );

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "message-status", 
                    2 => "not-is", 
                    3 => "pending"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('message-status' => array('$ne' => 'pending'))
            );
    }


    public function testFromFilterToQueryConditions_time()
    {
        $filterOperator = 'all';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "time", 
                    2 => "date-from", 
                    3 => "21/01/2012"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('timestamp' => array('$gt' => '2012-01-21T00:00:00'))
            );

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "time", 
                    2 => "date-to", 
                    3 => "21/01/2012"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('timestamp' => array('$lt' => '2012-01-21T00:00:00'))
            );
    }


    public function testFromFilterToQueryConditions_participantPhone()
    {
        $filterOperator = 'all';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "participant-phone", 
                    2 => "start-with", 
                    3 => "+255"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('participant-phone' => new MongoRegex("/^\\+255/"))
            );

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "participant-phone", 
                    2 => "equal-to", 
                    3 => "+255"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('participant-phone' => "+255")
            );

        
        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "participant-phone", 
                    2 => "start-with-any", 
                    3 => "+255, +256"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('$or' => array(
                array('participant-phone' => new MongoRegex("/^\\+255/")),
                array('participant-phone' => new MongoRegex("/^\\+256/"))
                ))
            );

    }

    
    public function testFromFilterToQueryConditions_messageContent()
    {
        $filterOperator = 'all';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "message-content", 
                    2 => "equal-to", 
                    3 => "content"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('message-content' => 'content')
            );

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "message-content", 
                    2 => "contain", 
                    3 => "content"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('message-content' => new MongoRegex('/content/i'))
            );

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "message-content", 
                    2 => "has-keyword", 
                    3 => "keyword"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('message-content' => new MongoRegex('/^keyword($| )/i'))
            );
    }

    public function testFromFilterToQueryConditions_dialogueSource()
    {
        $filterOperator = 'all';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "dialogue-source", 
                    2 => "is", 
                    3 => "1"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('dialogue-id' => '1')
            );
    }

    public function testFromFilterToQueryConditions_interactionSource()
    {
        $filterOperator = 'all';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "interaction-source", 
                    2 => "is", 
                    3 => "1"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('interaction-id' => '1')
            );
    }


    public function testFromFilterToQueryConditions_answer()
    {
        $filterOperator = 'all';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "answer", 
                    2 => "matching"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array(
                'message-direction' => 'incoming',
                'matching-answer' => array('$ne' => null))
            );

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "answer", 
                    2 => "not-matching"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array(
                'message-direction' => 'incoming',
                'matching-answer' => null)
            );
    }

    
    public function testFromFilterToQueryConditions_AND()
    {
        $filterOperator = 'all';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "participant-phone", 
                    2 => "equal-to", 
                    3 => "+255"),
                array(
                    1 => "participant-phone", 
                    2 => "equal-to", 
                    3 => "+256"),
                array(
                    1 => "participant-phone", 
                    2 => "equal-to", 
                    3 => "+257"),
            )); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('$and' => array(
                array('participant-phone' => "+255"),
                array('participant-phone' => "+256"),
                array('participant-phone' => "+257"),
                ))
            );       
    }

    public function testFromFilterToQueryConditions_OR()
    {
        $filterOperator = 'any';

        $filterParams = array(
            "filter_param" => array(
                array(
                    1 => "participant-phone", 
                    2 => "equal-to", 
                    3 => "+255"),
                array(
                    1 => "participant-phone", 
                    2 => "equal-to", 
                    3 => "+256"),
                array(
                    1 => "participant-phone", 
                    2 => "equal-to", 
                    3 => "+257"),
                )
            ); 
        $this->assertEqual(
            $this->History->fromFilterToQueryConditions($filterOperator, $filterParams),
            array('$or' => array(
                array('participant-phone' => "+255"),
                array('participant-phone' => "+256"),
                array('participant-phone' => "+257"),
                ))
            );       
    }

}
    