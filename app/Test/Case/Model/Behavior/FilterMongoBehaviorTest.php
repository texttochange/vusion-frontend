<?php
App::uses('MongoModel', 'Model');


class DummyMongo extends MongoModel 
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


class FilterMongoBehaviorTest extends CakeTestCase
{

	public function setUp() {
		$this->Model = $this->getMock('DummyMongo', array('fromFilterToQueryCondition', 'aCallback'));
		$this->Model->Behaviors->load('FilterMongo');
	}


    public function testValidateFilter_join()
    {
        $expected = array(
            'filter' => array(
                'filter_operator' => 'all',
                'filter_param' => array(
                    array(
                        1 => 'schedule', 
                        2 => 'are-present'))),
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


    public function testValidateFilter_join_onlyOne()
    {
        $expected = array(
            'filter' => array(
                'filter_operator' => 'all',
                'filter_param' => array(
                    array(
                        1 => 'schedule', 
                        2 => 'are-present'))),
            'joins' => array(array(
                'field' => 'phone',
                'model' => 'SecondDummyModel',
                'function' => 'getUniqueParticipantPhone',
                'parameters' => array())),
            'errors' => array('only one join is allowed'));

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'schedule', 
                    2 => 'are-present'),
                array(
                    1 => 'schedule', 
                    2 => 'are-present')));

        $checkedFilter = $this->Model->validateFilter($filter);
        $this->assertEqual($expected, $checkedFilter);
    }


    public function testValidateFilter_join_and_other()
    {
        $expected = array(
            'filter' => array(
                'filter_operator' => 'all',
                'filter_param' => array(
                    array(
                        1 => 'optin',
                        2 => 'now'),
                    array(
                        1 => 'schedule', 
                        2 => 'are-present'))),
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
                    1 => 'optin',
                    2 => 'now'),
                array(
                    1 => 'schedule', 
                    2 => 'are-present')));

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


    public function testFromFiltersToQuery_join() 
    {
        $expected = array('$and' => array(
            array('phone' => array('$in' => array('06', '07'))),
            array('participant-session-id' => array('$ne' => null))));

        $filters = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'optin', 
                    2 => 'now'),
                array(
                    1 => 'schedule', 
                    2 => 'are-present')));

        $this->Model
            ->expects($this->at(0))
            ->method('fromFilterToQueryCondition')
            ->with($filters['filter_param'][0])
            ->will($this->returnValue(
                array('participant-session-id' => array('$ne' => null))));

        $this->Model
            ->expects($this->at(1))
            ->method('fromFilterToQueryCondition')
            ->with($filters['filter_param'][1])
            ->will($this->returnValue(array()));


        $otherConditions = array('phone' => array('$in' => array('06', '07')));
        $result = $this->Model->fromFiltersToQueryCondition($filters, $otherConditions);

        $this->assertEqual($expected, $result);
    }


    public function testFindAllSafeJoin() 
    {
        $expectedQuery_1 = array(
            'limit' => 10,
            'conditions' => array(
                'phone' => array(
                    '$in' => array(
                        '+254100000000',
                        '+254100000001',
                        '+254100000002'))));

        $expectedQuery_2 = array(
            'limit' => 10,
            'conditions' => array(
                'phone' => array(
                    '$in' => array(
                        '+254100000003',
                        '+254100000004',
                        '+254100000005'))));

        $this->Model->MAX_JOIN_PHONES = 2;
        $iter = new ArrayIterator(array(
                    array("_id" => "+254100000000"),
                    array("_id" => "+254100000001"),
                    array("_id" => "+254100000002"),
                    array("_id" => "+254100000003"),
                    array("_id" => "+254100000004"),
                    array("_id" => "+254100000005")));
        $query = array(
            'limit' => 10,
            'conditions' => array(
                'phone' => array(
                    '$join' => $iter)));

        $query = $this->Model->findAllSafeJoin('before', $query);
        $this->assertEqual($expectedQuery_1, $query);

        $query = $this->Model->findAllSafeJoin('before', $query);
        $this->assertEqual($expectedQuery_2, $query);

        $results = $this->Model->findAllSafeJoin('after', $query, array());
        $this->assertEqual(array(), $results);
    }
    

    public function testCountSafeJoin()
    {
        $expectedConditions_1 = array(
            'phone' => array(
                '$in' => array(
                    '+254100000000',
                    '+254100000001',
                    '+254100000002')));

        $expectedConditions_2 = array(
            'phone' => array(
                '$in' => array(
                    '+254100000003',
                    '+254100000004',
                    '+254100000005')));

        $this->Model->MAX_JOIN_PHONES = 2;
        $iter = new ArrayIterator(array(
                    array("_id" => "+254100000000"),
                    array("_id" => "+254100000001"),
                    array("_id" => "+254100000002"),
                    array("_id" => "+254100000003"),
                    array("_id" => "+254100000004"),
                    array("_id" => "+254100000005")));
        $conditions = array(
            'phone' => array(
                '$join' => $iter));

        $this->Model
            ->expects($this->at(0))
            ->method('aCallback')
            ->with($expectedConditions_1, null, 30000)
            ->will($this->returnValue(3));
        $this->Model
            ->expects($this->at(1))
            ->method('aCallback')
            ->with($expectedConditions_2, null, 30000)
            ->will($this->returnValue(3));

        $this->assertEqual(
            6,
            $this->Model->countSafeJoin('aCallback', $conditions));
    }


    public function testHasJoin() 
    {
        
        $conditions = array(
            'phone' => array(
                '$join' => 'something'));
        $this->assertEqual(
            'something',
            FilterMongoBehavior::hasJoin($conditions));

        $conditions = array(
            '$and' => array(
                array(
                    'something' => array(
                        '$join' => 'something'))));
        $this->assertEqual(
            'something',
            FilterMongoBehavior::hasJoin($conditions));

        $conditions = array(
            '$or' => array(
                array(
                    'phone' => array(
                        '$join' => ''))));
        $this->assertEqual(
            '',
            FilterMongoBehavior::hasJoin($conditions));
    }


    public function testReplaceJoin() 
    {   
        $conditions = array(
            'phone' => array(
                '$join' => ''));
        $conditions = FilterMongoBehavior::replaceJoin($conditions, array('$in' => 'something'));
        $this->assertEqual(
            $conditions,
            array('phone' => array('$in' => 'something')));
        
        $conditions = array(
            '$and' => array(
                array(
                    'something' => array(
                        '$join' => ''))));
        $conditions = FilterMongoBehavior::replaceJoin($conditions, array('$in' => 'something'));
        $this->assertEqual(
            $conditions,
            array(
                '$and' => array(
                    array(
                        'something' => array(
                            '$in' => 'something')))));
    }

}