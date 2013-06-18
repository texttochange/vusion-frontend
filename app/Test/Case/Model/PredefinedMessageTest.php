<?php

App::uses('PredefinedMessage', 'Model');

class PredefinedMessageTestCase extends CakeTestCase
{
    
    
    public function setUp()
    {
        parent::setUp();

        $options                 = array('database' => 'testdbprogram');
        $this->PredefinedMessage = new PredefinedMessage($options);
        
        $this->dropData();
        
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        
        unset($this->PredefinedMessage);
        
        parent::tearDown();
    }
    
    
    public function dropData()
    {
        $this->PredefinedMessage->deleteAll(true, false);
    }
    
    
    public function testSave()
    {
        $predefinedMessage = array(
            'name' => '',
            'content' => 'hello'
            );
        $this->PredefinedMessage->create();
        $this->assertFalse($this->PredefinedMessage->save($predefinedMessage));
        $this->assertEquals('A predefined message must have a name.',
            $this->PredefinedMessage->validationErrors['name'][0]);
        
        $predefinedMessage02 = array(
            'name' => 'new message',
            'content' => ''
            );
        $this->PredefinedMessage->create();
        $this->assertFalse($this->PredefinedMessage->save($predefinedMessage02));
        $this->assertEquals('Please enter some content for this message.',
            $this->PredefinedMessage->validationErrors['content'][0]);
        
        $predefinedMessage03 = array(
            'name' => 'message one',
            'content' => 'hello'
            );
        $this->PredefinedMessage->create();
        $savedMessage = $this->PredefinedMessage->save($predefinedMessage03);
        $this->assertTrue(isset($savedMessage['PredefinedMessage']));
    }
    
    
    public function testSave_fail_nameNotUnique()
    {
        $predefinedMessage = array(
            'name' => 'message one',
            'content' => 'hello'
            );
        $this->PredefinedMessage->create();
        $savedMessage = $this->PredefinedMessage->save($predefinedMessage);
        
        $predefinedMessage02 = array(
            'name' => 'message one',
            'content' => 'bye bye'
            );
        $this->PredefinedMessage->create();
        $this->assertFalse($this->PredefinedMessage->save($predefinedMessage02));
        $this->assertEquals('This name already exists. Please choose another.',
            $this->PredefinedMessage->validationErrors['name'][0]);
    }

}
