<?php
App::uses('UnmatchableReply', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class UnmatchableReplyTestCase extends CakeTestCase
{

    
    public function setUp()
    {
        parent::setUp();

        $option         = array('database'=>'test');
        $this->UnmatchableReply = new UnmatchableReply($option);

        $this->UnmatchableReply->setDataSource('mongo_test');
        $this->UnmatchableReply->deleteAll(true, false);
    }


    public function tearDown()
    {
        $this->UnmatchableReply->deleteAll(true, false);
        unset($this->UnmatchableReply);
        parent::tearDown();
    }


    public function testFromFilterToQueryConditions_fromPhone()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'from-phone', 
                    2 => 'start-with', 
                    3 => '+255'),
                )
            ); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryConditions($filter),
            array('participant-phone' => new MongoRegex('/^\\+255/'))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'from-phone', 
                    2 => 'equal-to', 
                    3 => '+255'),
                )
            ); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryConditions($filter),
            array('participant-phone' => '+255')
            );

        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'from-phone', 
                    2 => 'start-with-any', 
                    3 => '+255, +256'),
                )
            ); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryConditions($filter),
            array('$or' => array(
                array('participant-phone' => new MongoRegex('/^\\+255/')),
                array('participant-phone' => new MongoRegex('/^\\+256/'))
                ))
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
            $this->UnmatchableReply->fromFilterToQueryConditions($filter),
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
            $this->UnmatchableReply->fromFilterToQueryConditions($filter),
            array('timestamp' => array('$lt' => '2012-01-21T00:00:00'))
            );
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
            $this->UnmatchableReply->fromFilterToQueryConditions($filter),
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
            $this->UnmatchableReply->fromFilterToQueryConditions($filter),
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
            $this->UnmatchableReply->fromFilterToQueryConditions($filter),
            array('message-content' => new MongoRegex('/^keyword($| )/i'))
            );
    }


}
