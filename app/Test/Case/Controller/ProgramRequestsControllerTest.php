<?php
App::uses('ProgramRequestsController', 'Controller');
App::uses('Request', 'Model');

class TestProgramRequestsController extends ProgramRequestsController
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }


}


class ProgramRequestsControllerTestCase extends ControllerTestCase
{
    var $programData = array(
            0 => array( 
                'Program' => array(
                    'name' => 'Test Name',
                    'url' => 'testurl',
                    'database' => 'testdbprogram'
                    )
                )
            );

     public function setUp()
    {
        parent::setUp();

        $this->Requests = new TestProgramRequestsController();
        ClassRegistry::config(array('ds' => 'test'));                
    }


    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->instanciateModels();
        $this->Request->deleteAll(true, false);        
    }

    
    protected function instanciateModels()
    {
        $options = array('database' => $this->programData[0]['Program']['database']);

        $this->Request = new Request($options);
    }

    
    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Requests);

        parent::tearDown();
    }

   
    public function testIndex()
    {
        $this->assertTrue(false);
    }


    public function testAdd()
    {
        $this->assertTrue(false);
    }


    public function testEdit()
    {
        $this->assertTrue(false);
    }


    public function testDelete()
    {
        $this->assertTrue(false);
    }


}
