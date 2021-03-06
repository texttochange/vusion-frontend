<?php
App::Uses('MongoModel', 'Model');
App::uses('UnmatchableReplyController', 'Controller');
App::uses('UnmatchableReply', 'Model');
App::uses('Export', 'Model');


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

    public function setup()
    {
        parent::setUp();
        
        $this->UnmatchableReplies = new TestUnmatchableReplyController();
        $this->instanciateUnmatchableReplyModel();
    }
    
    
    protected function instanciateUnmatchableReplyModel()
    {
        $this->UnmatchableReply = ClassRegistry::init('UnmatchableReply');
        $this->Export = ClassRegistry::init('Export');
    }
    
    
    protected function dropData()
    {
        $this->UnmatchableReply->deleteAll(true,false);
        $this->Export->deleteAll(true, false);
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        unset($this->UnmatchableReplies);
        parent::tearDown();
    }
    
    
    protected function mockProgramAccess()
    {
        $unmatchableReplys = $this->generate('UnmatchableReply', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read'),
                'Auth' => array('loggedIn')
                ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array('hasSpecificProgramAccess')
                ),
            'methods' => array(
                '_instanciateVumiRabbitMQ',
                '_notifyBackendExport'
                ),
            ));
        
        $unmatchableReplys->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue(true));

        $unmatchableReplys->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue(true));

        return $unmatchableReplys;
    }
    
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
    
    
    public function testExport()
    {
        $unmatchableReplys = $this->mockProgramAccess();
        $unmatchableReplys
            ->expects($this->once())
            ->method('_notifyBackendExport')
            ->with(
                  $this->matchesRegularExpression('/^[a-f0-9]+$/'))
            ->will($this->returnValue(true));

        $this->testAction("/unmatchableReply/export");

        $this->assertEqual($this->Export->find('count'), 1);
        $export = $this->Export->find('first');
        $this->assertTrue(isset($export['Export']));
        $this->assertContains(
            'Unmatchable_Reply_', 
            $export['Export']['file-full-name']);
    }


    public function testExported()
    {
        $this->mockProgramAccess();
        $this->Export->create();
        $this->Export->save(array(
            'database' => 'vusion',
            'collection' => 'unmatchable_reply',
            'file-full-name' => '/var/test.csv'));
        $this->Export->create();
        $this->Export->save(array(
            'database' => 'testdbprogram',
            'collection' => 'participants',
            'file-full-name' => '/var/test2.csv'));
        $this->Export->create();
        $this->Export->save(array(
            'database' => 'vusion',
            'collection' => 'unmatchable_reply',
            'file-full-name' => '/var/test3.csv'));
        $this->Export->create();
        $this->Export->save(array(
            'database' => 'testdbprogram2',
            'collection' => 'history',
            'file-full-name' => '/var/test3.csv'));

        $this->testAction("/testurl/programHistory/exported");
        $files = $this->vars['files'];
        $this->assertEqual(2, count($files));
    }
    
    
}
