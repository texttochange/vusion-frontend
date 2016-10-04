<?php
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('PredefinedMessage', 'Model');


class PredefinedMessageTestCase extends CakeTestCase
{
        
    public function setUp()
    {
        parent::setUp();
        $dbName = 'testdbprogram';
        $this->PredefinedMessage = ProgramSpecificMongoModel::init(
            'PredefinedMessage', $dbName);
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
    
    
    public function testApostrope_fail_content()
    {
        $predefinedMessage = array(
            'name' => 'message one',
            'content' => "’`’‘"
            );
        $this->PredefinedMessage->create();
        //$this->assertFalse($this->PredefinedMessage->save($predefinedMessage));
       // $this->assertEquals('The apostrophe used is not allowed.',
       //     $this->PredefinedMessage->validationErrors['content'][0]);
    }
    
    
    public function testValidContentVariable_fail()
    {
        $predefinedMessage = array(
            'name' => 'hello',
            'content' => 'There is a an [shoe.box] here.',
            );
        $this->PredefinedMessage->create();
        $this->assertFalse($this->PredefinedMessage->save($predefinedMessage));
       $this->assertEquals(
            "To be used as customized content, 'shoe' can only be either: participant, contentVariable or time.",
            $this->PredefinedMessage->validationErrors['content'][0]);
        
        $predefinedMessage['content'] = "Hello [participant.gender.name]";
        $this->assertFalse($this->PredefinedMessage->save($predefinedMessage));
        $this->assertEquals(
            "To be used in message, participant only accepts one key.",
            $this->PredefinedMessage->validationErrors['content'][0]);
        
        $predefinedMessage['content'] = "Hello [contentVariable.kampala.pork.male.price]";
        $this->assertFalse($this->PredefinedMessage->save($predefinedMessage));
        $this->assertEquals(
            "To be used in message, contentVariable only accepts maximum three keys.",
            $this->PredefinedMessage->validationErrors['content'][0]); 
    }
    
}
