<?php
App::uses('ProgramPredefinedMessagesController', 'Controller');

class TestProgramPredefinedMessagesController extends ProgramPredefinedMessagesController
{
    
    public $autoRender = false;
    
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
    
}


class ProgramPredefinedMessagesControllerTestCase extends ControllerTestCase
{
    /**
    * Data
    *
    */
    
    var $programData = array(
        0 => array( 
            'Program' => array(
                'name' => 'Test Name',
                'url' => 'testurl',
                'timezone' => 'utc',
                'database' => 'testdbprogram'
                )
            ));
    
    
    public function setUp()
    {
        parent::setUp();
        $this->ProgramPredefinedMessages = new ProgramPredefinedMessagesController();
        $this->dropData();
    }
    
    
    protected function dropData()
    {
        $this->instanciatePredefinedMessageModel();
        $this->PredefinedMessage->deleteAll(true, false);
    }
    
    
    protected function instanciatePredefinedMessageModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->PredefinedMessage = new PredefinedMessage($options);
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        unset($this->PredefinedMessage);
        parent::tearDown();
    }
    
    
    public function mock_program_access()
    {
        $predefinedMessages = $this->generate(
            'ProgramPredefinedMessages', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth' => array()
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    )
                )
            );
        
        $predefinedMessages->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $predefinedMessages->Program
        ->expects($this->any())
        ->method('find')
        ->will($this->returnValue($this->programData));
        
        $predefinedMessages->Session
        ->expects($this->any())
        ->method('read')
        ->will(
            $this->returnValue($this->programData[0]['Program']['database'])
            );
        
        return $predefinedMessages;
        
    }
    
    /**
    * Test methods
    *
    */    
    
    public function testIndex()
    {
        $predefinedMessages = $this->mock_program_access();  
        
        $predefinedMessage =  array(
            'PredefinedMessage' => array(
                'name' => 'my message',
                'content' => 'welcome!!!!'
                )
            );
        $this->PredefinedMessage->create();
        $savedMessage = $this->PredefinedMessage->save($predefinedMessage);
        
        $this->testAction("/testurl/programPredefinedMessages/index");
        $this->assertEquals(1, count($this->vars['predefinedMessages']));
    }
    
    
    public function testAdd()
    {
        $predefinedMessages = $this->mock_program_access();  
        
        $predefinedMessage =  array(
            'PredefinedMessage' => array(
                'name' => 'my message',
                'content' => 'Hello!!!!'
                )
            );
        $this->testAction(
            "/testurl/programPredefinedMessages/add", 
            array(
                'method' => 'post',
                'data' => $predefinedMessage
                )
            );
        $this->assertEquals(1, $this->PredefinedMessage->find('count'));
    }
    
    
    public function testEdit()
    {
        $predefinedMessages = $this->mock_program_access();  
        
        $predefinedMessage =  array(
            'PredefinedMessage' => array(
                'name' => 'my message',
                'content' => 'welcome!!!!'
                )
            );
        $this->PredefinedMessage->create();
        $savedMessage = $this->PredefinedMessage->save($predefinedMessage);
        
        $this->testAction(
            "/testurl/programPredefinedMessages/edit/".$savedMessage['PredefinedMessage']['_id'], 
            array(
                'method' => 'post',
                'data' => array(
                    'PredefinedMessage' => array(
                        'name' => 'test message',
                        'content' => 'Hello world'
                        )
                    )
                )
            );
        $this->PredefinedMessage->id = $savedMessage['PredefinedMessage']['_id']."";
        $predefinedMessage = $this->PredefinedMessage->read(); 
        $this->assertEquals(
            'Hello world',
            $predefinedMessage['PredefinedMessage']['content']
            );
    }
    
    
    public function testDelete()
    {
        $predefinedMessages = $this->mock_program_access();  
        
        $predefinedMessage =  array(
            'PredefinedMessage' => array(
                'name' => 'my message',
                'content' => 'welcome!!!!'
                )
            );
        $this->PredefinedMessage->create();
        $savedMessage = $this->PredefinedMessage->save($predefinedMessage);
        
        $this->testAction(
            "/testurl/programPredefinedMessages/delete/".$savedMessage['PredefinedMessage']['_id']);
        $this->assertEquals(0, $this->PredefinedMessage->find('count'));        
    }
    
}


