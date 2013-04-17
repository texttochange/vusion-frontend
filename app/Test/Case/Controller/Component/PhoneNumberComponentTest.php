<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('PhoneNumberComponent', 'Controller/Component');

class TestPhoneNumberComponentController extends Controller {
}


class PhoneNumberComponent extends CakeTestCase {

    public $PhoneNumberComponent = null;
    public $Controller = null;
    public $fixtures = array('app.program', 'app.user', 'app.programsUser');

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

    public function testGetCountriesPrefixes()
    {
        $countriesPrefixes = $this->PhoneNumberComponent->getCountryPrefixes();
        $this->assertEqual($countriesPrefixes['44'], 'France');
    }


}