<?php
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('PhoneNumberHelper', 'View/Helper');

class PhoneNumberHelperTest extends CakeTestCase {

    public $PhoneNumberRenderer = null;


    public function setUp() 
    {
        parent::setUp();
        $Controller = new Controller();
        $View = new View($Controller);
        $this->PhoneNumberRenderer = new PhoneNumberHelper($View);
    }


    public function testRemoveCountryCodeOfShortcode()
    {
        $countriesPrefixes = array(256 => 'Uganda');

        $this->assertEqual(
            'Uganda-8181',
            $this->PhoneNumberRenderer->replaceCountryCodeOfShortcode('256-8181', $countriesPrefixes));
        
           $this->assertEqual(
            'Unknown-8181',
            $this->PhoneNumberRenderer->replaceCountryCodeOfShortcode('123-8181', $countriesPrefixes));
    }

   
    public function testGetInternationalPrefix()
    {
        $countriesPrefixes = array(256 => 'Uganda');
        
        $this->assertEqual(
            '256',
            $this->PhoneNumberRenderer->getInternationalPrefix('+2566017001234', $countriesPrefixes)
            );

        $this->assertEqual(
            '256',
            $this->PhoneNumberRenderer->getInternationalPrefix('256-8181', $countriesPrefixes)
            );
    }

    public function testAddInternationalCodeToShortcode()
    {
        $this->assertEqual(
            '256-8181',
            $this->PhoneNumberRenderer->addInternationalCodeToShortcode('8181', '256')
            );

        $this->assertEqual(
            '256-8181',
            $this->PhoneNumberRenderer->addInternationalCodeToShortcode('256-8181', '256')
            );

        $this->assertEqual(
            '+256670334454',
            $this->PhoneNumberRenderer->addInternationalCodeToShortcode('+256670334454', '256')
            );
    }


    public function testIsShortcodeWithPrefix()
    {
        $this->assertTrue($this->PhoneNumberRenderer->isShortcodeWithPrefix('246-8888'));
        $this->assertFalse($this->PhoneNumberRenderer->isShortcodeWithPrefix('8888'));
        $this->assertFalse($this->PhoneNumberRenderer->isShortcodeWithPrefix('+312345'));
    }


    public function testIsLongcode()
    {
        $this->assertFalse($this->PhoneNumberRenderer->isLongcode('246-8888'));
        $this->assertFalse($this->PhoneNumberRenderer->isLongcode('8888'));
        $this->assertTrue($this->PhoneNumberRenderer->isLongcode('+312345'));
    }


    public function testIsShortcode()
    {
        $this->assertFalse($this->PhoneNumberRenderer->isShortcode('246-8888'));
        $this->assertTrue($this->PhoneNumberRenderer->isShortcode('8888'));
        $this->assertFalse($this->PhoneNumberRenderer->isShortcode('+312345'));
    }


}