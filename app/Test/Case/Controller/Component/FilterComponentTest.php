<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('FilterComponent', 'Controller/Component');
App::uses('ScriptMaker', 'Lib');
App::uses('History', 'Model');


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
                    2 => "start-with",
                    3 => ""
                    )
                )
            );
        $checkedFilter = $this->FilterComponent->checkFilterFields($filter);
        
        $this->assertEqual(
            $checkedFilter['filterErrors'][0],
            'phone start with');
    }
    
    
    public function testgetConditions_show_flash_on_filterMissing() 
    {
        $dummyHistory = $this->getMock('History');
        $dummySession = $this->getMock('Session', array('setFlash'));
        
        $this->FilterComponent->Controller->Session = $dummySession; 
        
        $this->Controller->params['url'] = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                1 => array(
                    1 => "phone", 
                    2 => "start-with",
                    3 => ""
                    ),
                2 => array(
                    1 => "optin", 
                    2 => ""
                    ),
                3 => array(
                    1 => "enrolled", 
                    2 => "in",
                    3 => "testop"
                    )
                )
            );
        
        $dummySession
        ->expects($this->any())
        ->method('setFlash')
        ->with(
            __('2 filter(s) ignored due to missing information: "phone start with, optin"'), 
            'default',
            array('class' => "message failure"));
        
        $this->FilterComponent->getConditions($dummyHistory);
        
        $checkedFilter = $this->FilterComponent->checkFilterFields($this->Controller->params['url']);
        
        $this->assertEqual(
            $checkedFilter['filter']['filter_param'],
            array(3 => array(
                1 => "enrolled", 
                2 => "in",
                3 => "testop"
                ))
            );
    }

}
