<?php

App::uses('StatusController', 'Controller');
App::uses('Program', 'Model');


/**
 * TestStatusControllerController *
 */
class TestStatusController extends StatusController
{
/**
 * Auto render
 *
 * @var boolean
 */
    public $autoRender = false;


/**
 * Redirect action
 *
 * @param mixed $url
 * @param mixed $status
 * @param boolean $exit
 * @return void
 */
    public function redirect($url, $status = null, $exit = true) {
        $this->redirectUrl = $url;
    }


}


/**
 * StatusController Test Case
 *
 */
class StatusControllerTestCase extends ControllerTestCase
{

/**
 * Data
 *
 */    
    var $programData = array(
            0 => array( 
                'Program' => array(
                    'name' => 'Test Name',
                    'country' => 'Test Country',
                    'timezone' => 'UTC',
                    'url' => 'testurl',
                    'database' => 'testdbprogram'
                )
            ));
    

/**
 * setUp methods
 *
 */
    public function setUp()
    {
        parent::setUp();

        $this->Status = new TestStatusController();
        ClassRegistry::config(array('ds' => 'test'));
        
        $this->dropData();        
        
    }


    protected function dropData()
    {
        //As this model is created on the fly, need to instantiate again
        $this->instanciateParticipantsStateModel();
        $this->Status->ParticipantsState->deleteAll(true, false);
    }


    protected function instanciateParticipantsStateModel()
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        $this->Status->ParticipantsState = new ParticipantsState($options);
    }


    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Status);

        parent::tearDown();
    }


    protected function mockProgramAccess()
    {
        $Status = $this->generate('Status', array(
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
                $this->programData[0]['Program']['name']
                ));
    }


    public function testPagination() 
    {

        $this->mockProgramAccess();

        $this->instanciateParticipantsStateModel();
        $this->Status->ParticipantsState->create();
        $this->Status->ParticipantsState->save(array(
            'phone' => '256712747841',
            'message' => 'Hello everyone!',
            'time' => '2012-02-08T12:20:43.882854'
            ));
        $this->Status->ParticipantsState->create();
        $this->Status->ParticipantsState->save(array(
            'phone' => '356774527841',
            'message' => 'Hello there!',
            'time' => '2013-02-08T12:20:43.882854'
            ));
        
        $this->testAction("/testurl/status/index/sort:phone/direction:desc");
        $this->assertEquals('356774527841', $this->vars['statuses'][0]['ParticipantsState']['phone']);
        

        $this->mockProgramAccess();

        $this->testAction("/testurl/status/index/sort:phone/direction:asc");
        $this->assertEquals('256712747841', $this->vars['statuses'][0]['ParticipantsState']['phone']);

    }


}
