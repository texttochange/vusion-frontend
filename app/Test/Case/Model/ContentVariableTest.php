<?php

App::uses('ContentVariable', 'Model');

class ContentVariableTestCase extends CakeTestCase
{
    
    
    public function setUp()
    {
        parent::setUp();

        $options               = array('database' => 'testdbprogram');
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
        $this->assertEqual(
            'contentvariable',
            $savedMessage['ContentVariable']['object-type']);
        
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

        ## value field is empty
        $contentVariable = array(
            'keys' => 'my.key',
            'value' => ''
            );
        $this->ContentVariable->create();
        $savedContentVariable = $this->ContentVariable->save($contentVariable);
        $this->assertTrue(isset($savedContentVariable['ContentVariable']));
        
    }


    public function testSave_fail_keyEmpty() 
    {
        ## keys field is empty
        $contentVariable = array(
            'keys' => '',
            'value' => '28C'
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable));
        $this->assertEquals(
            'The content variable has no keys. A minimum of 1 key and a maximum of 3 keys is allowed. A valid keys set is for example \"key1.key2\".',
            $this->ContentVariable->validationErrors['keys'][0]);
    }


    public function testSave_fail_tooManyKeys() 
    {
        $contentVariable = array(
            'keys' => 'key1.key2.key3.key4',
            'value' => '28C'
            );
        $this->ContentVariable->create();
        $this->assertFalse($this->ContentVariable->save($contentVariable));
        $this->assertEquals(
            'The content variable "key1.key2.key3.key4" has 4 keys. A minimum of 1 key and a maximum of 3 keys is allowed. A valid keys set is for example \"key1.key2\".',
            $this->ContentVariable->validationErrors['keys'][0]);
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

    
    public function testSave_fail_isUnique()
    {
        $contentVariable = array(
            'keys' => 'new.key',
            'value' => 'meat'
            );
        $this->ContentVariable->create();
        $savedContentVariable = $this->ContentVariable->save($contentVariable);
        
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

        $this->ContentVariable->create();
        $this->ContentVariable->id = $savedContentVariable['ContentVariable']['_id'];
        $updateContentVariable = $this->ContentVariable->save($contentVariable02);
        $this->assertTrue(isset($updateContentVariable['ContentVariable']));        
    }


    public function testSave_fail_nonMutableTable() 
    {
        ## keys field is empty
        $contentVariable = array(
            'keys' => 'mykey',
            'table' => 'mytable',
            'value' => '28C'
            );
        $this->ContentVariable->create();
        $savedContentVariable = $this->ContentVariable->save($contentVariable);

        $savedContentVariable['ContentVariable']['table'] = 'anotherTable';

        $this->assertFalse($this->ContentVariable->save($savedContentVariable));
        $this->assertEquals(
            'Editing table is not allowed.',
            $this->ContentVariable->validationErrors['table'][0]);
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
