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
        $this->Status->History->create('unattach-history');
        $this->Status->History->save(array(
            'participant-phone' => '256712747841',
            'message-content' => 'Hello everyone!',
            'timestamp' => '2012-02-08T12:20:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered',
            ));
        $this->Status->History->create('unattach-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527841',
            'message-content' => 'Hello there!',
            'timestamp' => '2013-02-08T12:20:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered',
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
            'participant-phone' => '+356774527841',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered'
            ));
        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '+356774527842',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered'
            ));
        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '+356774527841',
            'message-content' => 'feel good',
            'timestamp' => '2013-02-08T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'incoming',
            'matching-answer' => 'good'
            ));
        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '+356774527841',
            'message-content' => 'what is your name? send NAME <your name>',
            'timestamp' => '2013-02-09T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'outgoing',
            'message-status' => 'pending' 
            ));

        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '+356774527841',
            'message-content' => 'name',
            'timestamp' => '2013-02-10T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'incoming',
            'matching-answer' => null
            ));
        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=interaction-source&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=11&filter_param%5B2%5D%5B1%5D=date&filter_param%5B2%5D%5B2%5D=from&filter_param%5B2%5D%5B3%5D=01/01/2012");
        $this->assertEquals(3, count($this->vars['statuses']));
        $this->assertEquals('11', $this->vars['statuses'][0]['History']['interaction-id']);

        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=participant-phone&filter_param%5B1%5D%5B2%5D=start-with-any&filter_param%5B1%5D%5B3%5D=%2B356774527841, 0777");
        $this->assertEquals(4, count($this->vars['statuses']));
        $this->assertEquals('356774527841', $this->vars['statuses'][0]['History']['participant-phone']);

        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=answer&filter_param%5B1%5D%5B2%5D=not-matching");
        $this->assertEquals(1, count($this->vars['statuses']));
        $this->assertEquals('name', $this->vars['statuses'][0]['History']['message-content']);

        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=date&filter_param%5B1%5D%5B2%5D=from&filter_param%5B1%5D%5B3%5D=09/02/2013&filter_param%5B2%5D%5B1%5D=date&filter_param%5B2%5D%5B2%5D=to&filter_param%5B2%5D%5B3%5D=10/02/2013");
        $this->assertEquals(1, count($this->vars['statuses']));
        $this->assertEquals('what is your name? send NAME <your name>', $this->vars['statuses'][0]['History']['message-content']);

    }


    public function testMassDelete() {
        
        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527841',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered'
            ));
        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527842',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered'
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
            'message-status' => 'pending' 
            ));

        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527841',
            'message-content' => 'name',
            'timestamp' => '2013-02-10T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'incoming',
            'matching-answer' => null
            ));
        $this->mockProgramAccess();
        $this->testAction("/testurl/programHistory/delete?filter_operator=all&filter_param%5B1%5D%5B1%5D=message-direction&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=outgoing");
        $this->assertEquals(2, $this->Status->History->find('count'));
        $this->mockProgramAccess();
        $this->testAction("/testurl/programHistory/delete?filter_operator=all&filter_param%5B1%5D%5B1%5D=message-direction&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=incoming");
        $this->assertEquals(0, $this->Status->History->find('count'));
       
    }

     public function testMassDelete_failMissingFilterOperator() {
        
        $this->Status->History->create('dialogue-history');
        $this->Status->History->save(array(
            'participant-phone' => '356774527841',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered'
            ));
        
        $this->mockProgramAccess();
        try {
            $this->testAction("/testurl/programHistory/delete?filter_param%5B1%5D%5B1%5D=message-direction&filter_param%5B1%5D%5B2%5D=is&filter_param%5B1%5D%5B3%5D=outgoing");
            $this->failed('Missing filter operator should rise an exception.');
        } catch (FilterException $e) {
            $this->assertEqual($e->getMessage(), "Filter operator is missing or not allowed.");
        }
        $this->assertEquals(
            1,
            $this->Status->History->find('count'));
     }


}
