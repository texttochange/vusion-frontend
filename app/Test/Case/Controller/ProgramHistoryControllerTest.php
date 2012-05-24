<?php

App::uses('ProgramHistoryController', 'Controller');
App::uses('Program', 'Model');


class TestProgramHistoryController extends ProgramHistoryController
{

    public $autoRender = false;


    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }


}


class ProgramHistoryControllerTestCase extends ControllerTestCase
{


    var $programData = array(
            0 => array( 
                'Program' => array(
                    'name' => 'Test Name',
                    'url' => 'testurl',
                    'database' => 'testdbprogram'
                )
            ));
    

    public function setUp()
    {
        parent::setUp();

        $this->Status = new TestProgramHistoryController();
        ClassRegistry::config(array('ds' => 'test'));
        
        $this->dropData();        
        
    }


    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->instanciateHistoryModel();
        $this->Status->History->deleteAll(true, false);
    }


    protected function instanciateHistoryModel()
    {
        $options               = array('database' => $this->programData[0]['Program']['database']);
        $this->Status->History = new History($options);
    }


    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Status);

        parent::tearDown();
    }


    protected function mockProgramAccess()
    {
        $Status = $this->generate('ProgramHistory', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
            ),
        ));
        
        $Status->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        
        $Status->Program
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->programData));
            
        $Status->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls(
                '4', 
                '2',
                $this->programData[0]['Program']['database'],
                $this->programData[0]['Program']['name'],
                'Africa/Kampala',
                'testdbprogram'
                ));
    }


    public function testPagination() 
    {

        $this->mockProgramAccess();

        $this->instanciateHistoryModel();
        $this->Status->History->create();
        $this->Status->History->save(array(
            'phone' => '256712747841',
            'message' => 'Hello everyone!',
            'time' => '2012-02-08T12:20:43.882854'
            ));
        $this->Status->History->create();
        $this->Status->History->save(array(
            'phone' => '356774527841',
            'message' => 'Hello there!',
            'time' => '2013-02-08T12:20:43.882854'
            ));
        
        $this->testAction("/testurl/status/index/sort:phone/direction:desc");
        $this->assertEquals('356774527841', $this->vars['statuses'][0]['History']['phone']);
        

        $this->mockProgramAccess();

        $this->testAction("/testurl/status/index/sort:phone/direction:asc");
        $this->assertEquals('256712747841', $this->vars['statuses'][0]['History']['phone']);

    }


}
