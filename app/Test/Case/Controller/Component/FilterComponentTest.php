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
    public $name = "FirstDummy";
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
                'fromFiltersToQueryCondition',
                'mergeFilterConditions'));
        $this->Controller->SecondDummyModel = $this->getMock(
            'SecondDummyModel',
            array(
                'getUniqueParticipantPhone'));
        $this->FilterComponent->startup($this->Controller);
    }
    
    
    public function tearDown()
    { 
        unset($this->FilterComponent);
        parent::tearDown();
    }
    
    public function testGetConditions_emptyFilter() 
    {
        $this->Controller->params['url'] = array();
        $conditions = $this->FilterComponent->getConditions($this->Controller->FirstDummyModel);
        $this->assertEqual(array(), $conditions);
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
                        'filter_param' => array()),
                    'joins' => array(),
                    'errors' => array(
                        array('first field is missing')))));
        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('fromFiltersToQueryCondition')
            ->with(
                array(
                    'filter_operator' => 'all',
                    'filter_param' => array()))
            ->will($this->returnValue(array()));

        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('mergeFilterConditions')
            ->with(array(), array())
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
                        'filter_param' => array(
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
                    'filter_param' => array(
                        1 => array(
                            1 => "enrolled", 
                            2 => "in",
                            3 => "testop"))))
            ->will($this->returnValue(array('enrolled' => 'testtop')));

        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('mergeFilterConditions')
            ->with(array(), array('enrolled' => 'testtop'))
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
                        'filter_param' => array(
                            1 => array(
                                1 => "phone", 
                                2 => "is",
                                3 => "+06"),
                            2 => array(
                                1 => "schedule", 
                                2 => "are-present"))),
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

        $filterConditions = array(
            '$or' => array(
                array('phone' => '+06'),
                array('phone' => array(
                    '$in' => array('+07')))));
        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('fromFiltersToQueryCondition')
            ->with(
                array(
                    'filter_operator' => 'any',
                    'filter_param' => array(
                        1 => array(
                            1 => "phone", 
                            2 => "is",
                            3 => "+06"),
                         2 => array(
                            1 => "schedule", 
                            2 => "are-present"))), 
                array('phone' => array('$join' => array('+07'))))
            ->will($this->returnValue($filterConditions));

        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('mergeFilterConditions')
            ->with(array(), $filterConditions)
            ->will($this->returnValue($filterConditions));

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


    public function testGetConditions_defaultConditions() 
    {
        $filters = array(
            'filter_operator' => 'any',
            'filter_param' => array(
                1 => array(
                    1 => "phone", 
                    2 => "is",
                    3 => "+06")));
        $this->Controller->params['url'] = $filters;

        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('validateFilter')
            ->with($this->Controller->params['url'])
            ->will($this->returnValue(
                array(
                    'filter' => array(
                        'filter_operator' => 'any',
                        'filter_param' => array(
                            1 => array(
                                1 => "phone", 
                                2 => "is",
                                3 => "+06"))),
                    'joins' => array(),
                    'errors' => array())));

        $this->Controller->SecondDummyModel
            ->expects($this->never())
            ->method('getUniqueParticipantPhone');

        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('fromFiltersToQueryCondition')
            ->with(
                array(
                    'filter_operator' => 'any',
                    'filter_param' => array(
                        1 => array(
                            1 => "phone", 
                            2 => "is",
                            3 => "+06"))))
            ->will($this->returnValue(
                array('phone' => '+06')));

        $this->Controller->FirstDummyModel
            ->expects($this->once())
            ->method('mergeFilterConditions')
            ->with(
                array('session-id' => array('$ne' => null)), 
                array('phone' => '+06'))
            ->will($this->returnValue(array(
                '$and' => array(
                    array('session-id' => array('$ne' => null)), 
                    array('phone' => '+06')
                    ))));

        $conditions = $this->FilterComponent->getConditions(
            $this->Controller->FirstDummyModel,
            array('session-id' => array('$ne' => null)),
            array('SecondDummyModel' => $this->Controller->SecondDummyModel));
        
        $this->assertEqual(
            $conditions,
            array('$and' => array(
                array('session-id' => array('$ne' => null)),
                array('phone' => '+06'))));
    }
    
    
    public function testHasCondition_false() 
    {
        $filters = array(
            'filter_operator' => 'any',
            'filter_param' => array(
                1 => array(
                    1 => "phone", 
                    2 => "is",
                    3 => "+06")));
        $this->Controller->params['url'] = $filters;
        
        $this->assertFalse($this->FilterComponent->hasConditions());
    }
    
    
    public function testHasCondition_true() 
    {
        $filters = array();
        $this->Controller->params['url'] = $filters;
        
        $this->assertTrue($this->FilterComponent->hasConditions());
    }
    
    
    public function testAddDefaultCondition() 
    {
        $filters = array();
        $this->Controller->params['url'] = $filters;
        $this->FilterComponent->addDefaultCondition('status', 'is', 'running');
        $this->assertEqual(
            $this->Controller->params['url'],
            array(
                'filter_operator' => 'all',
                'filter_param' => array(
                    1 => array(
                        1 => 'status',
                        2 => 'is',
                        3 => 'running'
                        )
                    )
                )
            );
    }


}
