<?php
App::uses('MongoModel', 'Model');
App::uses('FilterException', 'Lib');


class Dummy extends MongoModel 
{
	var $name = "Dummy";
	var $specific = true;
   	public function getModelVersion() {}
   	public function getRequiredFields($objectType) {}

    var $filterOperatorOptions = array('all', 'any');
	public $filterFields = array(
        'phone' => array(
            'operators' => array(
                'is' => array(
                    'parameter-type' => 'text'))),
        'optin' => array(
            'operators' => array(
                'now' => array(
                    'parameter-type' => 'none'))),
		'schedule' => array(
            'operators' => array(
                'are-present' => array(
                    'parameter-type' => 'none',
                    'participant-filter' => 'true',
                    'unique' => true,
                    'join' => array(
                        'field' => 'phone',
                        'model' => 'SecondDummyModel',
                        'function' => 'getUniqueParticipantPhone',
                        'parameters' => array())))));
}


class FilterBehaviorTest extends CakeTestCase
{

	public function setUp() {
		$this->Model = $this->getMock('Dummy', array('fromFilterToQueryCondition'));
		$this->Model->Behaviors->load('Filter');
	}


	public function tearDown()
    {
    }


    public function testGetFilters() 
    {
    	$expected =  array(
    		'schedule' => array(
                'operators' => array(
                    'are-present' => array(
                        'parameter-type' => 'none'))));

    	$this->assertEqual($expected, $this->Model->getFilters('participant-filter'));
    	$this->assertEqual(array(), $this->Model->getFilters('something'));
    }


    public function testValidateFilter_joins()
    {
        $expected = array(
            'filter' => array(
                'filter_operator' => 'all',
                'filter_params' => array()),
            'joins' => array(array(
                'field' => 'phone',
                'model' => 'SecondDummyModel',
                'function' => 'getUniqueParticipantPhone',
                'parameters' => array())),
            'errors' => array());

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'schedule', 
                    2 => 'are-present')));

        $checkedFilter = $this->Model->validateFilter($filter);
        $this->assertEqual($expected, $checkedFilter);
    }


    public function testValidateFilter_errors()
    {
        $expected = array(
            'filter' => array(
                'filter_operator' => 'all',
                'filter_params' => array()),
            'joins' => array(),
            'errors' => array(array(
                'phone', 'is', 'missing parameter')));

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'phone', 
                    2 => 'is',
                    3 => '')));

        $checkedFilter = $this->Model->validateFilter($filter);
        $this->assertEqual($expected, $checkedFilter);
    }


    public function testValidateFilter_invalidOperator()
    {
        $expected = array(
            'filter' => array(
                'filter_operator' => 'all',
                'filter_params' => array()),
            'joins' => array(),
            'errors' => array(array(
                'schedule', 'something', 'not supported')));

         $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array( 
                array(
                    1 => 'schedule', 
                    2 => 'something')));

        $checkedFilter = $this->Model->validateFilter($filter);    
        $this->assertEqual(
            $expected,
            $checkedFilter);
    }


    public function testValidateFilter_noOperator()
    {
        $expected = array(
            'filter' => array(
                'filter_operator' => 'all',
                'filter_params' => array()),
            'joins' => array(),
            'errors' => array(array(
                'schedule', 'operator not defined')));
        
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array( 
                array(
                    1 => 'schedule', 
                    2 => null)));

        $checkedFilter = $this->Model->validateFilter($filter);
        $this->assertEqual(
            $expected,
            $checkedFilter);
    }


    public function testValidateFilter_noField()
    {
        $expected = array(
            'filter' => array(
                'filter_operator' => 'all',
                'filter_params' => array()),
            'joins' => array(),
            'errors' => array('condition is missing'));

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array( 
                array(
                    1 => null, 
                    2 => null)));

        $checkedFilter = $this->Model->validateFilter($filter);
        $this->assertEqual(
            $expected,
            $checkedFilter);
    }


    public function testValidateFilter_ok()
    {
        $expected = array(
            'filter' => array(
                'filter_operator' => 'all',
                'filter_params' => array(
                    array(
                        1 => 'optin', 
                        2 => 'now'))),
            'joins' => array(),
            'errors' => array());

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'optin', 
                    2 => 'now')));

        $checkedFilter = $this->Model->validateFilter($filter);
        $this->assertEqual($expected, $checkedFilter);
    }


    public function testFromFiltersToQueryCondition() 
    {
    	$filters = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'schedule', 
                    2 => 'are-present')));

    	$this->Model
    		->expects($this->once())
    		->method('fromFilterToQueryCondition')
    		->with($this->equalTo($filters['filter_param'][0]))
    		->will($this->returnValue(array()));

    	$this->assertEqual(array(), $this->Model->fromFiltersToQueryCondition($filters));
    }


    public function testFromFiltersToQueryCondition_all() 
    {
    	$filters = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'tag', 
                    2 => 'is',
                    3 => 'geek'),
                array(
                	1 => 'tag', 
                	2 => 'not-with', 
                	3 => 'nerd'),
                array(
                	1 => 'phone',
                	2 => 'is',
                	3 => '123456')));

    	$this->Model
    		->expects($this->at(0))
    		->method('fromFilterToQueryCondition')
    		->with($filters['filter_param'][0])
    		->will($this->returnValue(
    			array('tag' => 'geek')));

    	$this->Model
    		->expects($this->at(1))
    		->method('fromFilterToQueryCondition')
    		->with($filters['filter_param'][1])
    		->will($this->returnValue(
    			array('tag' => array('$ne' => 'nerd'))));

    	$this->Model
    		->expects($this->at(2))
    		->method('fromFilterToQueryCondition')
    		->with($filters['filter_param'][2])
    		->will($this->returnValue(
    			array('phone' => '123456')));

    	$expected = array('$and' => array(
    		array('tag' => 'geek'),
    		array('tag' => array('$ne' => 'nerd')),
    		array('phone' => '123456')));

    	$this->assertEqual($expected, $this->Model->fromFiltersToQueryCondition($filters));
    }


    public function testFromFiltersToQueryCondition_any() 
    {
    	$filters = array(
            'filter_operator' => 'any',
            'filter_param' => array(
                array(
                    1 => 'tag', 
                    2 => 'is',
                    3 => 'geek'),
                array(
                	1 => 'tag', 
                	2 => 'not-with', 
                	3 => 'nerd'),
                array(
                	1 => 'phone',
                	2 => 'is',
                	3 => '123456')));

    	$this->Model
    		->expects($this->at(0))
    		->method('fromFilterToQueryCondition')
    		->with($filters['filter_param'][0])
    		->will($this->returnValue(
    			array('tag' => 'geek')));

    	$this->Model
    		->expects($this->at(1))
    		->method('fromFilterToQueryCondition')
    		->with($filters['filter_param'][1])
    		->will($this->returnValue(
    			array('tag' => array('$ne' => 'nerd'))));

    	$this->Model
    		->expects($this->at(2))
    		->method('fromFilterToQueryCondition')
    		->with($filters['filter_param'][2])
    		->will($this->returnValue(
    			array('phone' => '123456')));

    	$expected = array('$or' => array(
    		array('tag' => 'geek'),
    		array('tag' => array('$ne' => 'nerd')),
    		array('phone' => '123456')));

    	$this->assertEqual($expected, $this->Model->fromFiltersToQueryCondition($filters));
    }


    public function testFromFiltersToQueryCondition_any_otherCondition() 
    {
        $expected = array('$or' => array(
            array('phone' => array('$in' => array('06', '07'))),
            array('tag' => 'geek'),
            array('phone' => '123456')));

        $filters = array(
            'filter_operator' => 'any',
            'filter_param' => array(
                  array(
                    1 => 'tag', 
                    2 => 'is',
                    3 => 'geek'),
                array(
                    1 => 'phone',
                    2 => 'is',
                    3 => '123456')));

        $this->Model
            ->expects($this->at(0))
            ->method('fromFilterToQueryCondition')
            ->with($filters['filter_param'][0])
            ->will($this->returnValue(
                array('tag' => 'geek')));

        $this->Model
            ->expects($this->at(1))
            ->method('fromFilterToQueryCondition')
            ->with($filters['filter_param'][1])
            ->will($this->returnValue(
                array('phone' => '123456')));

        $otherConditions = array('phone' => array('$in' => array('06', '07')));
            
        $result = $this->Model->fromFiltersToQueryCondition($filters, $otherConditions);
        $this->assertEqual($expected, $result);
    }

    public function testFromFiltersToQuery_all_otherConditions() 
    {
        $expected = array('$and' => array(
            array('phone' => array('$in' => array('06', '07'))),
            array('tag' => 'geek'),
            array('tag' => array('$ne' => 'nerd')),
            array('phone' => '123456')));

        $filters = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'tag', 
                    2 => 'is',
                    3 => 'geek'),
                array(
                    1 => 'tag', 
                    2 => 'not-with', 
                    3 => 'nerd'),
                array(
                    1 => 'phone',
                    2 => 'is',
                    3 => '123456')));

        $this->Model
            ->expects($this->at(0))
            ->method('fromFilterToQueryCondition')
            ->with($filters['filter_param'][0])
            ->will($this->returnValue(
                array('tag' => 'geek')));

        $this->Model
            ->expects($this->at(1))
            ->method('fromFilterToQueryCondition')
            ->with($filters['filter_param'][1])
            ->will($this->returnValue(
                array('tag' => array('$ne' => 'nerd'))));

        $this->Model
            ->expects($this->at(2))
            ->method('fromFilterToQueryCondition')
            ->with($filters['filter_param'][2])
            ->will($this->returnValue(
                array('phone' => '123456')));

        $otherConditions = array('phone' => array('$in' => array('06', '07')));
        $result = $this->Model->fromFiltersToQueryCondition($filters, $otherConditions);

        $this->assertEqual($expected, $result);
    }


}