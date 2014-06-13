<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('FilterComponent', 'Controller/Component');


class TestFilterComponentController extends Controller
{
    var $components = array('Filter');
}


class FilterComponentTest extends CakeTestCase
{
    public $FilterComponent = null;
    public $Controller      = null;
    
    
    public function setUp() 
    {
        parent::setUp();
        $Collection            = new ComponentCollection();
        $this->FilterComponent = new FilterComponent($Collection);
        $CakeRequest           = new CakeRequest();
        $CakeResponse          = new CakeResponse();
        $this->Controller      = new TestFilterComponentController($CakeRequest, $CakeResponse);
        $this->Controller->constructClasses();
        $this->FilterComponent->initialize($this->Controller);
    }
    
    
    public function tearDown()
    {
        unset($this->FilterComponent);
        parent::tearDown();
    }
    
    
    public function testFilterCheckField_firstField_empty() 
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => '', 
                    )
                )
            );
        
        $checkedFilter = $this->FilterComponent->checkFilterFields($filter);
        
        $this->assertEqual(
            $checkedFilter['filterErrors'][0],
            'first filter field is missing');
        
        $this->assertEqual(
            $checkedFilter['filter']['filter_param'],
            array());
    }
    
    
    public function testFilterCheckField_secondField_empty() 
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => "optin", 
                    2 => "", 
                    )
                )
            );
        $checkedFilter = $this->FilterComponent->checkFilterFields($filter);
        
        $this->assertEqual(
            $checkedFilter['filterErrors'][0],
            'optin');
    }
    
    
    public function testFilterCheckField_thirdField_empty() 
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => "phone", 
                    2 => "start with",
                    3 => ""
                    )
                )
            );
        $checkedFilter = $this->FilterComponent->checkFilterFields($filter);
        
        $this->assertEqual(
            $checkedFilter['filterErrors'][0],
            'phone start with');
    }
    
    
}
