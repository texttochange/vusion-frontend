<?php

App::uses('DynamicContent', 'Model');

class DynamicContentTestCase extends CakeTestCase
{
    
    
    public function setUp()
    {
        parent::setUp();

        $options                 = array('database' => 'testdbprogram');
        $this->DynamicContent = new DynamicContent($options);
        
        $this->dropData();
        
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        
        unset($this->DynamicContent);
        
        parent::tearDown();
    }
    
    
    public function dropData()
    {
        $this->DynamicContent->deleteAll(true, false);
    }
    
    
    public function testSave()
    {
        $dynamicContent = array(
            'key' => '',
            'value' => '28C'
            );
        $this->DynamicContent->create();
        $this->assertFalse($this->DynamicContent->save($dynamicContent));
        $this->assertEquals('Dynamic content must have a key.',
            $this->DynamicContent->validationErrors['key'][0]);
        
        $dynamicContent02 = array(
            'key' => 'a key',
            'value' => ''
            );
        $this->DynamicContent->create();
        $this->assertFalse($this->DynamicContent->save($dynamicContent02));
        $this->assertEquals('Please enter a value for this dynamic content.',
            $this->DynamicContent->validationErrors['value'][0]);
        
        $dynamicContent03 = array(
            'key' => 'myKey',
            'value' => 'myValue'
            );
        $this->DynamicContent->create();
        $savedMessage = $this->DynamicContent->save($dynamicContent03);
        $this->assertTrue(isset($savedMessage['DynamicContent']));
    }
    
    
    public function testSave_fail_keyNotUnique()
    {
        $dynamicContent = array(
            'key' => 'new key',
            'value' => 'meat'
            );
        $this->DynamicContent->create();
        $savedMessage = $this->DynamicContent->save($dynamicContent);
        
        $dynamicContent02 = array(
            'key' => 'new key',
            'value' => 'new value'
            );
        $this->DynamicContent->create();
        $this->assertFalse($this->DynamicContent->save($dynamicContent02));
        $this->assertEquals('This key already exists. Please choose another.',
            $this->DynamicContent->validationErrors['key'][0]);
    }

}
