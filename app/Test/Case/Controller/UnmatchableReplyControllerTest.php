<?php

App::uses('UnmatchableReplyController', 'Controller');


class TestUnmatchableReplyController extends UnmatchableReplyController
{
    public $autoRender = false;
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
    
}

Class UnmatchableReplyControllerTestCase extends ControllerTestCase
{
    var $databaseName = "testdbmongo";
    
    public function setup()
    {
        Configure::write("mongo_db",$this->databaseName);
        parent::setUp();
        
        $this->UnmatchableReplies = new TestUnmatchableReplyController();
        $this->instanciateUnmatchableReplyModel();
        $this->dropData();
    }
    
    
    protected function instanciateUnmatchableReplyModel()
    {
        $options = array('database'=>$this->databaseName);
        $this->UnmatchableReply = new UnmatchableReply($options);
    }
    
    
    protected function dropData()
    {
        $this->UnmatchableReply->deleteAll(true,false);
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        
        unset($this->UnmatchableReplies);
        
        parent::tearDown();
    }
    
    /*
    public function mockProgramAccess()
    {
        $unmatchableReplies = $this->generate('UnmatchableReplies', array(
            'components' => array(
                'Acl' => array('check'),
                ),
            ));
        
        $unmatchableReplies->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        return $unmatchableReplies;
    }
    */
    
    public function testFilter()
    {        
        $this->UnmatchableReply->create();
        $this->UnmatchableReply->save(array(
            'participant-phone'=>'1234567890',
            'to'=>'8181',
            'message-content'=>'FEE bad',
            'timestamp'=>'2012-12-07T15:20:23'
            ));
        $this->UnmatchableReply->create();
        $this->UnmatchableReply->save(array(
            'participant-phone'=>'9876543210',
            'to'=>'8181',
            'message-content'=>'FEE gd',
            'timestamp'=>'2012-10-20T10:30:43'
            ));
        $this->UnmatchableReply->create();
        $this->UnmatchableReply->save(array(
            'participant-phone'=>'1234567890',
            'to'=>'8181',
            'message-content'=>'FEL weak',
            'timestamp'=>'2012-09-07T12:20:43'
            ));
        
        $this->mockProgramAccess();
        $this->testAction("/unmatchableReply/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=date&filter_param%5B1%5D%5B2%5D=from&filter_param%5B1%5D%5B3%5D=01%2F10%2F2012");
        $this->assertEquals(2, count($this->vars['unmatchableReplies']));
        
        $this->mockProgramAccess();
        $this->testAction("/unmatchableReply/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=date&filter_param%5B1%5D%5B2%5D=to&filter_param%5B1%5D%5B3%5D=01%2F10%2F2012");
        $this->assertEquals(1, count($this->vars['unmatchableReplies']));
        
        $this->mockProgramAccess();
        $this->testAction("/unmatchableReply/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=from-phone&filter_param%5B1%5D%5B2%5D=equal-to&filter_param%5B1%5D%5B3%5D=9876543210");
        $this->assertEquals(1, count($this->vars['unmatchableReplies']));
        
        $this->mockProgramAccess();
        $this->testAction("/unmatchableReply/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=message-content&filter_param%5B1%5D%5B2%5D=contain&filter_param%5B1%5D%5B3%5D=fee");
        $this->assertEquals(2, count($this->vars['unmatchableReplies']));
    }
    

    public function testPaginationCount()
    {
        $this->mockProgramAccess();
        $this->testAction("/unmatchableReply/paginationCount.json");
        $this->assertEqual($this->vars['paginationCount'], 0);
    }

    
}
