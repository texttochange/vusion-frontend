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

    
    public function testGetNameByPrefixes()
    {
        $countriesPrefixes = $this->CountryComponent->getNamesByPrefixes();
        $this->assertEqual($countriesPrefixes['33'], 'France');
        $this->assertEqual($countriesPrefixes['1242'], 'Bahamas');
    }

    
    public function testGetNamesByNames()
    {
        $countriesPrefixes = $this->CountryComponent->getNamesByNames();
        $this->assertEqual($countriesPrefixes['France'], 'France');
    }


    public function testGetPrefixesByNames()
    {
        $prefixesOfCountries = $this->CountryComponent->getPrefixesByNames();
        $this->assertEqual($prefixesOfCountries['France'], 33);
    }
    

    public function testFromPrefixedCodeToName() 
    {
        $this->assertEqual('France', $this->CountryComponent->fromPrefixedCodeToName('33-8181'));
        $this->assertEqual('Netherlands', $this->CountryComponent->fromPrefixedCodeToName('+31345433'));
        try {
            $this->CountryComponent->fromPrefixedCodeToName('+999999');
            $this->fail();
        } catch (VusionException $e){
            $this->assertEqual($e->getMessage(), "Cannot find valid country prefix from +999999.");
        }
    }   

    public function testFromPrefixToIso() 
    {
        $this->assertEqual('FRA', $this->CountryComponent->fromPrefixToIso('33'));
    }

    
}