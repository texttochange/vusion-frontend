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
        $contentVariable02 = array(
            'keys' => 'my.key',
            'value' => ''
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable02));
        $this->assertEquals('Please enter a value for this dynamic content.',
            $this->ContentVariable->validationErrors['value'][0]);
        
        ## value field has special characters
        $contentVariable02 = array(
            'keys' => 'my.key',
            'value' => '#$@*good'
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable02));
        $this->assertEquals("Use only DOT, space, letters and numbers for a value, e.g 'new value1'.",
            $this->ContentVariable->validationErrors['value'][0]);
        
        $contentVariable03 = array(
            'keys' => 'my.Key',
            'value' => 'myValue'
            );
        $this->ContentVariable->create();
        $savedMessage = $this->ContentVariable->save($contentVariable03);
        $this->assertTrue(isset($savedMessage['ContentVariable']));
        
        #single key
        $contentVariable04 = array(
            'keys' => 'myKey',
            'value' => 'myValue'
            );
        $this->ContentVariable->create();
        $otherSavedMessage = $this->ContentVariable->save($contentVariable04);
        $this->assertTrue(isset($otherSavedMessage['ContentVariable']));
    }
    
    
    public function testSave_fail_keyNotUnique()
    {
        $contentVariable = array(
            'keys' => 'new.key',
            'value' => 'meat'
            );
        $this->ContentVariable->create();
        $this->ContentVariable->save($contentVariable);
        
        $splitOrdercontentVariable = array(
            'keys' => 'key.new',
            'value' => 'somethingelse'
            );
        $this->ContentVariable->create();
        $savedMessage = $this->ContentVariable->save($splitOrdercontentVariable);
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
