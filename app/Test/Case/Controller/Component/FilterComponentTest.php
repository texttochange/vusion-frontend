<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('FilterComponent', 'Controller/Component');
App::uses('ScriptMaker', 'Lib');
App::uses('History', 'Model');
App::uses('MongoModel', 'Model');



class TestFilterComponentController extends Controller
{
    var $components = array('Filter');
}


class FirstDummyModel extends MongoModel 
{
    var $name = "FirstDummy";
    var $specific = true;
    public function getModelVersion() {}
    public function getRequiredFields($objectType) {}

    public $filterFields = array(
        'phone' => array(
            'operators' => array(
                'is' => array(
                    'parameter-type' => 'text'))),
        'optin' => array(
            'operators' => array(
                'now' => array(
                    'parameter-type' => 'none'))),
        'enrolled' => array(
            'operators' => array(
                'in' => array(
                    'parameter-type' => 'text'))),
        'schedule' => array(
            'operators' => array(
                'are-present' => array(
                    'parameter-type' => 'none',
                    'join' => array(
                        'field' => 'phone',
                        'model' => 'SecondDummyModel',
                        'function' => 'getUniqueParticipantPhone')))));
}


class SecondDummyModel extends MongoModel 
{
    var $name = "SecondDummy";
    var $specific = true;
    public function getModelVersion() {}
    public function getRequiredFields($objectType) {}

    public $filterFields = array(
        'schedule' => array(
            'operators' => array(
                'are-present' => array(
                    'find-type' => 'aggregate-phone',
                    'parameter-type' => 'none',
                    'participant-filter' => true))));
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
        $this->Controller->FirstDummyModel = $this->getMock(
            'FirstDummyModel',
            array(
                'validateFilter',
                'fromFiltersToQueryCondition'));
        //$this->Controller->FirstDummyModel->Behaviors->load('Filter');
        $this->Controller->SecondDummyModel = $this->getMock(
            'SecondDummyModel',
            array(
                'getUniqueParticipantPhone'));
        //$this->Controller->SecondDummyModel->Behaviors->load('Filter');
        $this->FilterComponent->initialize($this->Controller);
    }
    
    
    public function tearDown()
    { 
        unset($this->FilterComponent);
        parent::tearDown();
    }
    
    
    public function testGetConditions_fail_firstFieldEmpty() 
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => '')));
        $this->Controller->params['url'] = $filter;
        
        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('validateFilter')
            ->with($this->Controller->params['url'])
            ->will($this->returnValue(
                array(
                    'filter' => array(
                        'filter_operator' => 'all',
                        'filter_params' => array()),
                    'joins' => array(),
                    'errors' => array(
                        array('first field is missing')))));
        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('fromFiltersToQueryCondition')
            ->with(
                array(
                    'filter_operator' => 'all',
                    'filter_params' => array()))
            ->will($this->returnValue(array()));

        $dummySession = $this->getMock('Session', array('setFlash'));
        $dummySession
            ->expects($this->any())
            ->method('setFlash')
            ->with(__('1 filter(s) ignored due to missing information: "first field is missing"'));
        $this->FilterComponent->Controller->Session = $dummySession; 

        $conditions = $this->FilterComponent->getConditions($this->Controller->FirstDummyModel);
                
        $this->assertEqual(array(), $conditions);
    }
        
    
    public function testGetConditions_fail_invalidFilterParams() 
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                1 => array(
                    1 => "phone", 
                    2 => "start-with",
                    3 => ""),        //empty string for parameter => invalid
                2 => array(
                    1 => "optin", 
                    2 => ""),         //empty string for operator => invalid
                3 => array(
                    1 => "enrolled", 
                    2 => "in",
                    3 => "testop")
                ));
        $this->Controller->params['url'] = $filter;

        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('validateFilter')
            ->with($this->Controller->params['url'])
            ->will($this->returnValue(
                array(
                    'filter' => array(
                        'filter_operator' => 'all',
                        'filter_params' => array(
                            1 => array(
                                1 => "enrolled", 
                                2 => "in",
                                3 => "testop"))),
                    'joins' => array(),
                    'errors' => array(
                        array('phone', 'start-with'),
                        'optin'))));
        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('fromFiltersToQueryCondition')
            ->with(
                array(
                    'filter_operator' => 'all',
                    'filter_params' => array(
                        1 => array(
                            1 => "enrolled", 
                            2 => "in",
                            3 => "testop"))))
            ->will($this->returnValue(array('enrolled' => 'testtop')));

        $dummySession = $this->getMock('Session', array('setFlash'));
        $dummySession
            ->expects($this->any())
            ->method('setFlash')
            ->with(__('2 filter(s) ignored due to missing information: "phone start with, optin"'));
        $this->FilterComponent->Controller->Session = $dummySession; 

        $conditions = $this->FilterComponent->getConditions($this->Controller->FirstDummyModel);
               
        $this->assertEqual(array('enrolled' => 'testtop'), $conditions);
    }

    public function testGetConditions_joins() 
    {
        $filters = array(
            'filter_operator' => 'any',
            'filter_param' => array(
                1 => array(
                    1 => "phone", 
                    2 => "is",
                    3 => "+06"),
                2 => array(
                    1 => "schedule", 
                    2 => "are-present")));

        $this->Controller->params['url'] = $filters;

        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('validateFilter')
            ->with($this->Controller->params['url'])
            ->will($this->returnValue(
                array(
                    'filter' => array(
                        'filter_operator' => 'any',
                        'filter_params' => array(
                            1 => array(
                                1 => "phone", 
                                2 => "is",
                                3 => "+06"))),
                    'joins' => array(array(
                        'filter_operator' => 'any',
                        'field' => 'phone',
                        'model' => 'SecondDummyModel',
                        'function' => 'getUniqueParticipantPhone',
                        'parameters' => array('crusor' => false))),
                    'errors' => array())));

        $this->Controller->SecondDummyModel
            ->expects($this->once())
            ->method('getUniqueParticipantPhone')
            ->with(array('crusor' => false))
            ->will($this->returnValue(array('+07')));

        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('fromFiltersToQueryCondition')
            ->with(
                array(
                    'filter_operator' => 'any',
                    'filter_params' => array(
                        1 => array(
                            1 => "phone", 
                            2 => "is",
                            3 => "+06"))), 
                array(array('phone' => array('$join' => array('+07')))))
            ->will($this->returnValue(
                array('$or' => array(
                    array('phone' => '+06'),
                    array('phone' => array(
                        '$in' => array('+07')))))));

        $conditions = $this->FilterComponent->getConditions(
            $this->Controller->FirstDummyModel,
            array(),
            array('SecondDummyModel' => $this->Controller->SecondDummyModel));
                
        $this->assertEqual(
            $conditions,
            array('$or' => array(
                array('phone' => '+06'),
                array('phone' => array(
                        '$in' => array('+07'))))));
    }


}
