<?php
App::uses('ProgramPredefinedMessagesController', 'Controller');
App::uses('ProgramPredefinedMessages', 'Model');

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
    
    
    public function testIndex()
    {
    }
    
    
    public function testView()
    {
    }
    
    
    public function testAdd()
    {
    }
    
    
    public function testEdit()
    {
    }
    
    
    public function testDelete()
    {
    }
    
}


