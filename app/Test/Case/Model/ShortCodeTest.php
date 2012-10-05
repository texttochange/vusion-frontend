<?php
App::uses('ShortCode', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');


class ShortCodeTestCase extends CakeTestCase
{
    
    public function setUp()
    {
        parent::setUp();

        $option         = array('database'=>'test');
        $this->ShortCode = new ShortCode($option);

        $this->ShortCode->setDataSource('mongo_test');
        $this->ShortCode->deleteAll(true, false);
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
            'supported-internationally' => "0",
            'support-customized-id' => "1"
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
            'supported-internationally' => 1
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
            );
        
        $caymanShortCode = array(
            'country' => 'Cayman Islands',
            'shortcode' => '8282',
            'international-prefix' => '1345',
            );

        $this->ShortCode->create();
        $savedShortCode = $this->ShortCode->save($usShortCode);
        $this->assertTrue(isset($savedShortCode['ShortCode']));
        $this->ShortCode->create();
        $this->assertFalse($this->ShortCode->save($caymanShortCode));
    }

}