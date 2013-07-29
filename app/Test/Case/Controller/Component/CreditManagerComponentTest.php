<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('CreditManagerComponent', 'Controller/Component');


class TestCreditManagerController extends Controller 
{

    var $components = array('CreditManager');


    function constructClasses() 
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1');
        $this->redisProgramPrefix = 'unittest';
    }


}


class CreditManagerComponentTest extends CakeTestCase
{

    public $CreditManagerComponent = null;
    public $Controller = null;

    
    public function setUp() 
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->CreditManagerComponent = new CreditManagerComponent($Collection);
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();

        $this->Controller = new TestCreditManagerController($CakeRequest, $CakeResponse);
        // Don't get why but me need to manually call the constructClasses function
        $this->Controller->constructClasses();
        // We also need to call the callback
        $this->CreditManagerComponent->initialize($this->Controller);
 
        // Retrive the redis object from the controller
        $this->redis = $this->Controller->redis;
    }


    public function tearDown()
    {
        $keys = $this->redis->keys('unittest:*');
        foreach ($keys as $key) {
            $this->redis->delete($key);
        }
    }


    public function mkStatus($status) {
        return array(
            'object-type' => 'credit-status',
            'model-version' => '1',
            'since' => '2013-01-01T10:10:10',
            'status' => $status);
    }


    public function testGetStatus() 
    {
        $status = $this->mkStatus('ok');
        $key = "unittest:programdatabase:creditmanager:status"; 
        $this->redis->set($key, json_encode($status));

        $this->assertEqual(
            $status,
            $this->CreditManagerComponent->getStatus('programdatabase')
            );
    }


    public function testGetStatus_fail() 
    {
        $this->assertEqual(
            null,
            $this->CreditManagerComponent->getStatus('programdatabase')
            );
    }

    
    public function testGetCount() 
    {
        $key = "unittest:programdatabase:creditmanager:count"; 
        $this->redis->set($key, 10);

        $this->assertEqual(
            10,
            $this->CreditManagerComponent->getCount('programdatabase')
            );
    }


    public function testGetCount_fail() 
    {
        $this->assertEqual(
            null,
            $this->CreditManagerComponent->getCount('programdatabase')
            );
    }


}