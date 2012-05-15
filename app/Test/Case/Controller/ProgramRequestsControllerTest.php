<?php
App::uses('ProgramRequestsController', 'Controller');
App::uses('Request', 'Model');
App::uses('ScriptMaker', 'Lib');

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

        $this->Maker = new ScriptMaker();
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

    public function mockProgramAccess()
    {
        $requests = $this->generate(
            'ProgramRequests', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth' => array()
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                )
            );
        
        $requests->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));

        $requests->Session
            ->expects($this->any())
            ->method('read')
            ->will(
                $this->onConsecutiveCalls(
                    '4',
                    '2',
                    $this->programData[0]['Program']['database'], 
                    $this->programData[0]['Program']['name'],
                    $this->programData[0]['Program']['name'],
                    'utc',
                    'testdbprogram'
                    )
                );

        $requests->Program
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($this->programData));
            
        return $requests;
    }
   

    public function testIndex()
    {
        $this->mockProgramAccess();
        $this->testAction("testurl/programRequests/index");   
        $this->assertEqual(array(), $this->vars['requests']);
    }


    public function testAdd()
    {
        $requests = $this->mockProgramAccess();
        $requests->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('The request has been saved.');

        $request = $this->Maker->getOneRequest();
       
        $this->testAction(
            "testurl/programRequests/add",
            array(
                'method' => 'post',
                'data' => $request
                )
            );
    }


    public function testEdit()
    {
        $requests = $this->mockProgramAccess();
        $requests->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('The request has been saved.');

        $request = $this->Maker->getOneRequest();

        $this->instanciateModels();
        $this->Request->create();
        $savedRequest = $this->Request->save($request);
        $savedRequest['Request']['keyword'] = 'OTHERKEYWORD';
        
        $this->testAction(
            "testurl/programRequests/edit/" . $savedRequest['Request']['_id'],
            array(
                'method' => 'post',
                'data' => $savedRequest
                )
            );

        $this->assertEquals('OTHERKEYWORD', $requests->data['Request']['keyword']);
    }


    public function testDelete()
    {
        $requests = $this->mockProgramAccess();
        $requests->Session
            ->expects($this->once())
            ->method('setFlash')
            ->with('The request has been deleted.');

        $request = $this->Maker->getOneRequest();

        $this->instanciateModels();
        $this->Request->create();
        $savedRequest = $this->Request->save($request);

        $this->testAction("testurl/programRequests/delete/" . $savedRequest['Request']['_id']);     
    }


}
