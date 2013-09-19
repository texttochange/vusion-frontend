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
    
    
    public function testSave_ok()
    {      
        #two key
        $contentVariable = array(
            'keys' => 'my.Key',
            'value' => 'myValue'
            );
        $this->ContentVariable->create();
        $savedMessage = $this->ContentVariable->save($contentVariable);
        $this->assertTrue(isset($savedMessage['ContentVariable']));
        
        #single key
        $contentVariable = array(
            'keys' => 'myKey',
            'value' => 'myValue'
            );
        $this->ContentVariable->create();
        $otherSavedMessage = $this->ContentVariable->save($contentVariable);
        $this->assertTrue(isset($otherSavedMessage['ContentVariable']));

        #already array of keys
        $contentVariable = array(
            'keys' => array('last', 'Key'),
            'value' => 'myValue'
            );
        $this->ContentVariable->create();
        $lastSavedMessage = $this->ContentVariable->save($contentVariable);
        $this->assertTrue(isset($lastSavedMessage['ContentVariable']));

    }

    public function testSave_fail_empty() 
    {
        ## keys field is empty
        $contentVariable = array(
            'keys' => '',
            'value' => '28C'
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable));
        $this->assertEquals('The correct format is "key" or "key.key".',
            $this->ContentVariable->validationErrors['keys'][0]);
        
        ## value field is empty
        $contentVariable = array(
            'keys' => 'my.key',
            'value' => ''
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable));
        $this->assertEquals('Please enter a value for this dynamic content.',
            $this->ContentVariable->validationErrors['value'][0]);
    }

    
    public function testSave_fail_valueSpecialCharacter() {
        ## value field has special characters
        $contentVariable = array(
            'keys' => 'my.key',
            'value' => '#$@*good'
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable));
        $this->assertEquals("Use only DOT, space, letters and numbers for a value, e.g 'new value1'.",
            $this->ContentVariable->validationErrors['value'][0]);
    }
    
    public function testSave_fail_keyNotUnique()
    {
        $contentVariable = array(
            'keys' => 'new.key',
            'value' => 'meat'
            );
        $this->ContentVariable->create();
        $this->ContentVariable->save($contentVariable);
        
        $splitOrderContentVariable = array(
            'keys' => 'key.new',
            'value' => 'somethingelse'
            );
        $this->ContentVariable->create();
        $savedMessage = $this->ContentVariable->save($splitOrderContentVariable);
        $this->assertTrue(isset($savedMessage['ContentVariable']));

        $contentVariable02 = array(
            'keys' => 'new.key',
            'value' => 'new value'
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable02));
        $this->assertEquals('This keys pair already exists. Please choose another.',
            $this->ContentVariable->validationErrors['keys'][0]);
    }


    public function test_findContentVariableFromKeys()
    {
        $contentVariable = array(
            'keys' => 'new.key',
            'value' => 'meat'
            );
        $this->ContentVariable->create();
        $this->ContentVariable->save($contentVariable);
        
        $foundContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('new', 'key'))));
        $this->assertEqual('meat', $foundContentVariable[0]['ContentVariable']['value']);

        $foundContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => array('key', 'new'))));
        $this->assertEqual(0, count($foundContentVariable));
    }


}
