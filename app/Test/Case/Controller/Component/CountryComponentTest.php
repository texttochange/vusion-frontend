<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('CountryComponent', 'Controller/Component');


class TestCountryComponentController extends Controller
{
}


class CountryComponentTest extends CakeTestCase {

    public $CountryComponent = null;
    public $Controller = null;
    
    
    public function setUp() 
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->CountryComponent = new CountryComponent($Collection);
        //Don't get why the useDbConfig is not properly configure by ClassResigtry
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        $this->Controller = new TestCountryComponentController($CakeRequest, $CakeResponse);
        $this->CountryComponent->startup($this->Controller);
    }

    
    public function tearDown() 
    {
        parent::tearDown();
        unset($this->CountryComponent);
        unset($this->Controller);
    }

    
    public function testGetCountriesByPrefixes()
    {
        $countriesPrefixes = $this->CountryComponent->getCountriesByPrefixes();
        $this->assertEqual($countriesPrefixes['33'], 'France');
        $this->assertEqual($countriesPrefixes['1242'], 'Bahamas');
    }

    
    public function testGetCountries()
    {
        $countriesPrefixes = $this->CountryComponent->getCountries();
        $this->assertEqual($countriesPrefixes['France'], 'France');
    }


    public function testGetPrefixesByCountries()
    {
        $prefixesOfCountries = $this->CountryComponent->getPrefixesByCountries();
        $this->assertEqual($prefixesOfCountries['France'], 33);
    }
    

    public function testFromPrefixedCodeToCountry() 
    {
        $this->assertEqual('France', $this->CountryComponent->fromPrefixedCodeToCountry('33-8181'));
        $this->assertEqual('Netherlands', $this->CountryComponent->fromPrefixedCodeToCountry('+31345433'));
        try {
            $this->CountryComponent->fromPrefixedCodeToCountry('+999999');
            $this->fail();
        } catch (VusionException $e){
            $this->assertEqual($e->getMessage(), "Cannot find valid country prefix from +999999.");
        }
    }   
    
}