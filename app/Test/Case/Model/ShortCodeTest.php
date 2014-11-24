<?php
App::uses('ShortCode', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class ShortCodeTestCase extends CakeTestCase
{
    
    public function setUp()
    {
        parent::setUp();
        $this->ShortCode = ClassRegistry::init('ShortCode');
    }
    
    
    public function tearDown()
    {
        $this->ShortCode->deleteAll(true, false);
        unset($this->ShortCode);
        parent::tearDown();
    }
    
    
    public function testSave()
    {
        $emptyShortCode = array();
        
        $wrongShortCode = array(
            'shortcode' => '8282 ',
            'international-prefix' => ' 256',
            'country' => 'uganda',
            'badfield' => 'something',
            'supported-internationally' => '0',
            'support-customized-id' => '1',
            'max-character-per-sms' => '160',
            );
        
        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($emptyShortCode);
        $this->assertEqual($emptyShortCode, array()); ##Todo how come it's an array
        
        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($wrongShortCode);
        $this->assertFalse(array_key_exists('badfield', $savedShortCode['ShortCode']));
        $this->assertEqual('8282',$savedShortCode['ShortCode']['shortcode']);
        $this->assertEqual(0, $savedShortCode['ShortCode']['supported-internationally']);
        $this->assertEqual(1, $savedShortCode['ShortCode']['support-customized-id']);
        
        ## Cannot save the same couple coutry/shortcode if not supported internationally
        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($wrongShortCode);
        $this->assertFalse($savedShortCode);
        
        ## Supported Internationally
        $supportedInternationally = array(
            'country' => 'netherland',
            'shortcode' => '8282',
            'international-prefix' => '31',
            'supported-internationally' => 1,
            'max-character-per-sms' => '160',
            );
        
        # Cannot save an international shortcode while another local shortcode is using the code
        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($supportedInternationally);
        $this->assertFalse($savedShortCode);
        
        # Cannot save an internatinal shortcode while another international with same code is register
        $supportedInternationally['shortcode'] = '+311234546';
        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($supportedInternationally);
        $this->assertTrue(isset($savedShortCode['ShortCode']));
        
        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($supportedInternationally);
        $this->assertFalse($savedShortCode);
        
        # Cannot save an national shortcode in countries that share the same international prefix
        $usShortCode = array(
            'country' => 'United States',
            'shortcode' => '8282',
            'international-prefix' => '1',
            'max-character-per-sms' => '160',
            );
        
        $caymanShortCode = array(
            'country' => 'Cayman Islands',
            'shortcode' => '8282',
            'international-prefix' => '1345',
            'max-character-per-sms' => '160',
            );
        
        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($usShortCode);
        $this->assertTrue(isset($savedShortCode['ShortCode']));
        $this->ShortCode->create();
        $this->assertFalse($this->ShortCode->save($caymanShortCode));
    }
    
    public function testSave_fail()
    {
        
        $shortCode = array(
            'country' => 'Cayman Islands',
            'shortcode' => '8282',
            'international-prefix' => '256',
            'max-character-per-sms' => '145',
            );
        
        $this->ShortCode->create();
        $this->assertFalse($this->ShortCode->save($shortCode));
        $this->assertEqual(
            $this->ShortCode->validationErrors['max-character-per-sms'][0],
            'The valid value are only 70, 140 and 160.');
    }
    
    
    public function testEdit_Shortcode_And_Country_fail()
    { 
        $shortCode = array(
            'country' => 'Uganda',
            'shortcode' => '8282',  
            'international-prefix' => '256',    
            'max-character-per-sms' => '140',
            );
        
        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($shortCode);
        
        $savedShortCode['ShortCode']['shortcode'] = '8285';
        $savedShortCode['ShortCode']['country']   = 'Kenya';
        $savedShortCode['ShortCode']['international-prefix']   = '254';
        $this->ShortCode->create();
        $editShortCodeAndCountry = $this->ShortCode->save($savedShortCode);
        
        $this->assertFalse($editShortCodeAndCountry);
        $this->assertEqual(
            $this->ShortCode->validationErrors['shortcode'][0],
            'This field is read only.');
        $this->assertEqual(
            $this->ShortCode->validationErrors['country'][0],
            'This field is read only.');
        $this->assertEqual(
            $this->ShortCode->validationErrors['international-prefix'][0],
            'This field is read only.');
    }
    
}