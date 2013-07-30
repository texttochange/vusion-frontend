<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('PhoneNumberComponent', 'Controller/Component');


class TestPhoneNumberComponentController extends Controller {
}


class PhoneNumberComponentTest extends CakeTestCase {

    public $PhoneNumberComponent = null;
    public $Controller = null;
    
    public function setUp() 
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->PhoneNumberComponent = new PhoneNumberComponent($Collection);
        //Don't get why the useDbConfig is not properly configure by ClassResigtry
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        $this->Controller = new TestPhoneNumberComponentController($CakeRequest, $CakeResponse);
        $this->PhoneNumberComponent->startup($this->Controller);
    }

    public function tearDown() {
        parent::tearDown();
        unset($this->PhoneNumberComponent);
        unset($this->Controller);
    }

    public function testGetCountriesByPrefixes()
    {
        $countriesPrefixes = $this->PhoneNumberComponent->getCountriesByPrefixes();
        $this->assertEqual($countriesPrefixes['33'], 'France');
    }

    public function testGetCountries()
    {
        $countriesPrefixes = $this->PhoneNumberComponent->getCountries();
        $this->assertEqual($countriesPrefixes['France'], 'France');
    }



}