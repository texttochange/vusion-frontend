<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('PhoneNumberComponent', 'Controller/Component');


class TestPhoneNumberComponentController extends Controller
{
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

    
    public function tearDown() 
    {
        parent::tearDown();
        unset($this->PhoneNumberComponent);
        unset($this->Controller);
    }

    
    public function testGetCountriesByPrefixes()
    {
        $countriesPrefixes = $this->PhoneNumberComponent->getCountriesByPrefixes();
        $this->assertEqual($countriesPrefixes['33'], 'France');
        $this->assertEqual($countriesPrefixes['1 242'], 'Bahamas');
    }

    
    public function testGetCountries()
    {
        $countriesPrefixes = $this->PhoneNumberComponent->getCountries();
        $this->assertEqual($countriesPrefixes['France'], 'France');
    }


    public function testGetPrefixesByCountries()
    {
        $prefixesOfCountries = $this->PhoneNumberComponent->getPrefixesByCountries();
        $this->assertEqual($prefixesOfCountries['France'], 33);
    }
    

    public function testFromPrefixedCodeToCountry() 
    {
        $this->assertEqual('France', $this->PhoneNumberComponent->fromPrefixedCodeToCountry('33-8181'));
        $this->assertEqual('Netherlands', $this->PhoneNumberComponent->fromPrefixedCodeToCountry('+31345433'));
        try {
            $this->PhoneNumberComponent->fromPrefixedCodeToCountry('+999999');
            $this->fail();
        } catch (VusionException $e){
            $this->assertEqual($e->getMessage(), "Cannot find valid country prefix from +999999.");
        }
    }   
    
}