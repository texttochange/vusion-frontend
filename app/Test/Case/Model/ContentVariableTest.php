<?php

App::uses('ContentVariable', 'Model');

class ContentVariableTestCase extends CakeTestCase
{
    
    
    public function setUp()
    {
        parent::setUp();

        $options                 = array('database' => 'testdbprogram');
        $this->ContentVariable = new ContentVariable($options);
        
        $this->dropData();
        
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        
        unset($this->ContentVariable);
        
        parent::tearDown();
    }
    
    
    public function dropData()
    {
        $this->ContentVariable->deleteAll(true, false);
    }
    
    
    public function testSave()
    {
        $contentVariable = array(
            'key' => '',
            'value' => '28C'
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable));
        $this->assertEquals('Dynamic content must have a key.',
            $this->ContentVariable->validationErrors['key'][0]);
        
        $contentVariable02 = array(
            'key' => 'a key',
            'value' => ''
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable02));
        $this->assertEquals('Please enter a value for this dynamic content.',
            $this->ContentVariable->validationErrors['value'][0]);
        
        $contentVariable03 = array(
            'key' => 'myKey',
            'value' => 'myValue'
            );
        $this->ContentVariable->create();
        $savedMessage = $this->ContentVariable->save($contentVariable03);
        $this->assertTrue(isset($savedMessage['ContentVariable']));
    }
    
    
    public function testSave_fail_keyNotUnique()
    {
        $contentVariable = array(
            'key' => 'new key',
            'value' => 'meat'
            );
        $this->ContentVariable->create();
        $savedMessage = $this->ContentVariable->save($contentVariable);
        
        $contentVariable02 = array(
            'key' => 'new key',
            'value' => 'new value'
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable02));
        $this->assertEquals('This key already exists. Please choose another.',
            $this->ContentVariable->validationErrors['key'][0]);
    }

}
