<?php

App::uses('ProgramHistoryController', 'Controller');
App::uses('Program', 'Model');
App::uses('ScriptMaker', 'Lib');


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
        $this->Maker = new ScriptMaker();

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

        $this->instanciateHistoryModel();
        $this->Status->History->create('default-history');
        $this->Status->History->save(array(
            'participant-phone' => '256712747841',
            'message-content' => 'Hello everyone!',
            'timestamp' => '2012-02-08T12:20:43.882854'
            ));
        $this->Status->History->create('default-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527841',
            'message-content' => 'Hello there!',
            'timestamp' => '2013-02-08T12:20:43.882854'
            ));

        $this->mockProgramAccess();        
        $this->testAction("/testurl/history/index/sort:participant-phone/direction:desc");
        $this->assertEquals('356774527841', $this->vars['statuses'][0]['History']['participant-phone']);
        

        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index/sort:participant-phone/direction:asc");
        $this->assertEquals('256712747841', $this->vars['statuses'][0]['History']['participant-phone']);

    }


    public function testFilter()
    {

        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527841',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            ));
        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527841',
            'message-content' => 'feel good',
            'timestamp' => '2013-02-08T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'incoming',
            'matching-answer' => 'good'
            ));
        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527841',
            'message-content' => 'what is your name? send NAME <your name>',
            'timestamp' => '2013-02-09T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'outgoing',
            ));

        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527841',
            'message-content' => 'name',
            'timestamp' => '2013-02-10T12:20:43.882854',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'incoming',
            'matching-answer' => null
            ));
        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_param%5B1%5D%5B1%5D=dialogue&filter_param%5B1%5D%5B2%5D=1&filter_param%5B1%5D%5B3%5D=11&filter_param%5B2%5D%5B1%5D=date-from&filter_param%5B2%5D%5B2%5D=01/01/2012");
        $this->assertEquals(2, count($this->vars['statuses']));
        $this->assertEquals('11', $this->vars['statuses'][0]['History']['interaction-id']);

        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_param%5B1%5D%5B1%5D=participant-phone&filter_param%5B1%5D%5B2%5D=356");
        $this->assertEquals(4, count($this->vars['statuses']));
        $this->assertEquals('356774527841', $this->vars['statuses'][0]['History']['participant-phone']);

        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_param%5B1%5D%5B1%5D=non-matching-answers");
        $this->assertEquals(1, count($this->vars['statuses']));
        $this->assertEquals('356774527842', $this->vars['statuses'][0]['History']['participant-phone']);


    }


}
