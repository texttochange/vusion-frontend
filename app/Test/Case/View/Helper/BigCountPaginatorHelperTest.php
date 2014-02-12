<?php
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('BigCountPaginatorHelper', 'View/Helper');

class BigCountPaginatorHelperTest extends CakeTestCase
{

    public $BigCounterPaginatorHelper = null;


    public function setUp()
    {
        parent::setUp();
        $controller = null;
        $this->View = new View($controller);
        $this->Paginator = new BigCountPaginatorHelper($this->View);
        $this->Paginator->request = new CakeRequest(null, false);
    }
    
    
    public function tearDown() {
		unset($this->View, $this->Paginator);
	}

    
    public function testCounter_totalCount_calculated() 
    {
        $this->Paginator->request->addParams(
            array(
                'paging' => array(
                    'History' => array(
                        'page' => 1,
                        'current' => 0,
                        'count' => 20,
                        'nextPage' => true,
                        'pageCount' => 2,
                        'limit' => 10))));
        $this->assertEqual(
            "1 10 20",
            $this->Paginator->counter("{:start} {:end} {:count}"));

        $this->assertTrue($this->Paginator->hasNext());
    }

    
    public function testCounter_totalCount_displayed() 
    {
        $this->Paginator->request->addParams(
            array(
                'paging' => array(
                    'History' => array(
                        'page' => 1,
                        'current' => 0,
                        'count' => 0,
                        'nextPage' => true,
                        'pageCount' => 0,
                        'limit' => 10))));
        $this->assertEqual(
            "0 0 0",
            $this->Paginator->counter("{:start} {:end} {:count}"));

        $this->assertTrue($this->Paginator->hasNext());
    }


    public function testCounter_totalCount_notCalculated() 
    {
        $this->Paginator->request->addParams(
            array(
                'paging' => array(
                    'History' => array(
                        'page' => 1,
                        'current' => 0,
                        'count' => 'many',
                        'pageCount' => null,
                        'limit' => 10))));        
        $this->assertEqual(
            '1 10 many',
            $this->Paginator->counter("{:start} {:end} {:count}"));

        $this->assertTrue($this->Paginator->hasNext());
    }


}