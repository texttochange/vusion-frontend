<?php
App::uses('UnmatchableReply', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class UnmatchableReplyTestCase extends CakeTestCase
{
    
    
    public function setUp()
    {
        parent::setUp();
        $this->UnmatchableReply = ClassRegistry::init('UnmatchableReply');
    }
    
    
    public function tearDown()
    {
        $this->UnmatchableReply->deleteAll(true, false);
        unset($this->UnmatchableReply);
        parent::tearDown();
    }
    
    
    public function testfromFilterToQueryCondition_fromPhone()
    {
        $filterParam = array(
            1 => 'from-phone', 
            2 => 'start-with', 
            3 => '+255'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('participant-phone' => new MongoRegex('/^\\+255/')));
        
        $filterParam = array(
            1 => 'from-phone', 
            2 => 'equal-to', 
            3 => '+255'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('participant-phone' => '+255'));
        
        
        $filterParam = array(
            1 => 'from-phone', 
            2 => 'start-with-any', 
            3 => '+255, +256'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('$or' => array(
                array('participant-phone' => new MongoRegex('/^\\+255/')),
                array('participant-phone' => new MongoRegex('/^\\+256/'))
                ))
            );
        
    }
    
    
    public function testfromFilterToQueryCondition_toPhone()
    {
        $filterParam = array(
            1 => 'to-phone', 
            2 => 'start-with', 
            3 => '+255'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('to' => new MongoRegex('/^\\+255/')));
        
        $filterParam = array(
            1 => 'to-phone', 
            2 => 'equal-to', 
            3 => '+255'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('to' => '+255'));
    }
    

    public function testfromFilterToQueryCondition_country()
    {
        $filterParam = array(
            1 => 'country', 
            2 => 'is', 
            3 => 'Uganda');
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('participant-phone' => new MongoRegex('/^(\\+)?256/')));
    }
    
    public function testfromFilterToQueryCondition_shortcode()
    {
        $filterParam = array(
            1 => 'shortcode', 
            2 => 'is', 
            3 => '8181'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('$or' => array(
                array('participant-phone' => new MongoRegex('/\\d*-8181$/')),
                array('to' => '8181')))
            );
    }
    
    public function testfromFilterToQueryCondition_date()
    {
        $filterParam = array(
            1 => 'date', 
            2 => 'from', 
            3 => '21/01/2012'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('timestamp' => array('$gt' => '2012-01-21T00:00:00')));
        
        $filterParam = array(
            1 => 'date', 
            2 => 'to', 
            3 => '21/01/2012'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('timestamp' => array('$lt' => '2012-01-21T00:00:00')));
    }
    
    
    public function testfromFilterToQueryCondition_messageContent()
    {
        $filterParam = array(
            1 => 'message-content', 
            2 => 'equal-to', 
            3 => 'content'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('message-content' => 'content'));
        
        $filterParam = array(
            1 => 'message-content', 
            2 => 'contain', 
            3 => 'content'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('message-content' => new MongoRegex('/content/i')));
        
        $filterParam = array(
            1 => 'message-content', 
            2 => 'has-keyword', 
            3 => 'keyword'); 
        $this->assertEqual(
            $this->UnmatchableReply->fromFilterToQueryCondition($filterParam),
            array('message-content' => new MongoRegex('/^keyword($| )/i')));
    }
    
    
}
