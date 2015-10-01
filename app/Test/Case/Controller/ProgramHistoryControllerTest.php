<?php
App::uses('ProgramHistoryController', 'Controller');
App::uses('Program', 'Model');
App::uses('ScriptMaker', 'Lib');
App::uses('TestHelper', 'Lib');
App::uses('ProgramSpecificMongoModel', 'Model');


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
                'name' => 'Test Name?good/for|testing &me%',
                'url' => 'testurl',
                'database' => 'testdbprogram',
                'status' => 'running'
                )
            ));
    
    
    public function setUp()
    {
        parent::setUp();
        
        $this->Histories = new TestProgramHistoryController();
        
        $dbName = $this->programData[0]['Program']['database'];
        $this->History = ProgramSpecificMongoModel::init(
            'History', $dbName, true);
        $this->ProgramSetting = ProgramSpecificMongoModel::init(
            'ProgramSetting', $dbName, true);
        $this->Export = ClassRegistry::init('Export');
        
        $this->Maker = new ScriptMaker();
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');      
        
    }
    
    
    protected function dropData()
    {
        $this->History->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
        $this->Export->deleteAll(true, false);
        TestHelper::deleteAllProgramFiles('testurl');
    }    


    public function tearDown()
    {
        $this->dropData();        
        unset($this->Histories);
        parent::tearDown();
    }
    
    
    protected function mockProgramAccess_withoutSession()
    {
        $histories = $this->generate('ProgramHistory', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read'),
                'Auth' => array('loggedIn')
                ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
                ),
            'methods' => array(
                '_instanciateVumiRabbitMQ',
                '_notifyBackendExport',
                )
            ));
        
        $histories->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $histories->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue('true'));

        $histories->Program
        ->expects($this->once())
        ->method('find')
        ->will($this->returnValue($this->programData));
        
        return $histories;
    }
    
    
    protected function mockProgramAccess()
    {
        $histories = $this->mockProgramAccess_withoutSession();
        
        $histories->Session
        ->expects($this->any())
        ->method('read')
        ->will(
            $this->returnValue(
                $this->programData[0]['Program']['database']
                )
            );
        
        return $histories;
    }
    

    public function testIndex_order() 
    {
        $history_1 = array(
            'object-type' => 'unattach-history',
            'participant-phone' => '256712747841',
            'message-content' => 'Hello everyone!',
            'timestamp' => '2012-02-08T12:20:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_1);
        $this->History->save($history_1);

        $history_2 = array(
            'object-type' => 'unattach-history',
            'participant-phone' => '356774527841',
            'message-content' => 'Hello there!',
            'timestamp' => '2013-02-08T12:20:43.882854',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_2);
        $this->History->save($history_2);
        
        $this->mockProgramAccess();        
        $this->testAction("/testurl/history/index/sort:participant-phone/direction:desc");
        $this->assertEquals('356774527841', $this->vars['histories'][0]['History']['participant-phone']);
        
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index/sort:participant-phone/direction:asc");
        $this->assertEquals('256712747841', $this->vars['histories'][0]['History']['participant-phone']);   
    }
    
    
    public function testIndex_filter()
    {   
        $history_1 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '+356774527841',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_1);
        $this->History->save($history_1);

        $history_2 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '+356774527842',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_2);
        $this->History->save($history_2);

        $history_3 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '+356774527841',
            'message-content' => 'feel good',
            'timestamp' => '2013-02-08T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'incoming',
            'matching-answer' => 'good');
        $this->History->create($history_3);
        $this->History->save($history_3);

        $history_4 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '+356774527841',
            'message-content' => 'what is your name? send NAME <your name>',
            'timestamp' => '2013-02-09T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'outgoing',
            'message-status' => 'pending');
        $this->History->create($history_4);
        $this->History->save($history_4);
        
        $history_5 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '+356774527841',
            'message-content' => 'name',
            'timestamp' => '2013-02-10T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'incoming',
            'matching-answer' => null);
        $this->History->create($history_5);
        $this->History->save($history_5);
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_operator=all&filter_param[1][1]=interaction-source&filter_param[1][2]=is&filter_param[1][3]=11&filter_param[2][1]=date&filter_param[2][2]=from&filter_param[2][3]=01/01/2012");
        $this->assertEquals(3, count($this->vars['histories']));
        $this->assertEquals('11', $this->vars['histories'][0]['History']['interaction-id']);
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_operator=all&filter_param[1][1]=participant-phone&filter_param[1][2]=start-with-any&filter_param[1][3]=%2B356774527841, 0777");
        $this->assertEquals(4, count($this->vars['histories']));
        $this->assertEquals('356774527841', $this->vars['histories'][0]['History']['participant-phone']);
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_operator=all&filter_param[1][1]=answer&filter_param[1][2]=not-matching");
        $this->assertEquals(1, count($this->vars['histories']));
        $this->assertEquals('name', $this->vars['histories'][0]['History']['message-content']);
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_operator=all&filter_param[1][1]=date&filter_param[1][2]=from&filter_param[1][3]=09/02/2013&filter_param[2][1]=date&filter_param[2][2]=to&filter_param[2][3]=10/02/2013");
        $this->assertEquals(1, count($this->vars['histories']));
        $this->assertEquals('what is your name? send NAME <your name>', $this->vars['histories'][0]['History']['message-content']);
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/history/index?filter_operator=any&".
            "filter_param[1][1]=participant-phone&filter_param[1][2]=equal-to&filter_param[1][3]=%2B356774527842&".
            "filter_param[2][1]=message-direction&filter_param[2][2]=is&filter_param[2][3]=incoming");
        $this->assertEquals(3, count($this->vars['histories']));        
    }


    public function testListHistory()
    {
        $history_1 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '+356774527841',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_1);
        $this->History->save($history_1);

        $history_2 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '+356774527842',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_2);
        $this->History->save($history_2);

        $this->mockProgramAccess();
        $this->testAction("/testurl/history/listHistory.json?filter_operator=all&filter_param[1][1]=participant-phone&filter_param[1][2]=equal-to&filter_param[1][3]=%2B356774527841");
        $this->assertEquals(1, count($this->vars['histories']));
        $this->assertEquals('+356774527841', $this->vars['histories'][0]['History']['participant-phone']);
    }


    public function testMassDelete() 
    {    
        $history_1 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '356774527841',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_1);
        $this->History->save($history_1);

        $history_2 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '356774527842',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history_2);
        $this->History->save($history_2);

        $history_3 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '356774527841',
            'message-content' => 'feel good',
            'timestamp' => '2013-02-08T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'incoming',
            'matching-answer' => 'good');
        $this->History->create($history_3);
        $this->History->save($history_3);

        $history_4 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '356774527841',
            'message-content' => 'what is your name? send NAME <your name>',
            'timestamp' => '2013-02-09T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'outgoing',
            'message-status' => 'pending');
        $this->History->create($history_4);
        $this->History->save($history_4);
        
        $history_5 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '356774527841',
            'message-content' => 'name',
            'timestamp' => '2013-02-10T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'incoming',
            'matching-answer' => null);
        $this->History->create($history_5);
        $this->History->save($history_5);

        $history_6 = array(
            'object-type' => 'oneway-marker-history',
            'participant-phone' => '356774527841',
            'dialogue-id' => '1',
            'interaction-id' => '12');
        $this->History->create($history_6);
        $this->History->save($history_6);
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programHistory/delete?filter_operator=all&filter_param[1][1]=message-direction&filter_param[1][2]=is&filter_param[1][3]=outgoing");
        $this->assertEquals(3, $this->History->find('count'));
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programHistory/delete?filter_operator=all&filter_param[1][1]=message-direction&filter_param[1][2]=is&filter_param[1][3]=incoming");
        $this->assertEquals(1, $this->History->find('count'));
        
        $history_7 = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '356774527841',
            'message-content' => 'name',
            'timestamp' => '2013-02-10T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '12',
            'message-direction' => 'incoming',
            'matching-answer' => null);
        $this->History->create($history_7);
        $this->History->save($history_7);
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programHistory/delete");
        $this->assertEquals(1, $this->History->find('count'));   
    }
    
    
    public function testMassDelete_failMissingFilterOperator() 
    {
        $history = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => '356774527841',
            'message-content' => 'How are you? send FEEL GOOD or FEEL BAD',
            'timestamp' => '2013-02-07T12:20:43',
            'dialogue-id' => '1',
            'interaction-id' => '11',
            'message-direction' => 'outgoing',
            'message-status' => 'delivered');
        $this->History->create($history);
        $this->History->save($history);
        
        $this->mockProgramAccess();
        try {
            $this->testAction("/testurl/programHistory/delete?filter_param[1][1]=message-direction&filter_param[1][2]=is&filter_param[1][3]=outgoing");
            $this->failed('Missing filter operator should rise an exception.');
        } catch (FilterException $e) {
            $this->assertEqual($e->getMessage(), "Filter operator is missing.");
        }
        $this->assertEquals(
            1,
            $this->History->find('count'));
    }
    
    
    public function testExport()
    {
        $historys = $this->mockProgramAccess();

        $expectedConditions = array('$or' => array(
            array('object-type' => array('$in' => $this->History->messageType)),
            array('object-type' => array('$exists' => false))));
        $historys
            ->expects($this->once())
            ->method('_notifyBackendExport')
            ->with(
               $this->matchesRegularExpression('/^[a-f0-9]+$/'))
            ->will($this->returnValue(true));
        $this->testAction("/testurl/programHistory/export");

        $this->assertEqual($this->Export->find('count'), 1);
        $export = $this->Export->find('first');
        $this->assertTrue(isset($export['Export']));
        $this->assertContains(
            'Test_Name_good_for_testing_me_history_', 
            $export['Export']['file-full-name']);
        $this->assertEquals(
            $expectedConditions,
            $export['Export']['conditions']);
    }


    public function testPaginationCount()
    {
        $this->mockProgramAccess();
        $this->testAction("/testurl/programHistory/paginationCount.json");
        $this->assertEqual($this->vars['paginationCount'], 0);
    }


    public function testExported()
    {
        $this->mockProgramAccess();

        $this->Export->create();
        $this->Export->save(array(
            'database' => 'testdbprogram',
            'collection' => 'history',
            'file-full-name' => '/var/test.csv'));
        $this->Export->create();
        $this->Export->save(array(
            'database' => 'testdbprogram',
            'collection' => 'participants',
            'file-full-name' => '/var/test2.csv'));
        $this->Export->create();
        $this->Export->save(array(
            'database' => 'testdbprogram2',
            'collection' => 'participants',
            'file-full-name' => '/var/test3.csv'));
        $this->Export->create();
        $this->Export->save(array(
            'database' => 'testdbprogram2',
            'collection' => 'history',
            'file-full-name' => '/var/test3.csv'));


        $this->testAction("/testurl/programHistory/exported");
        $files = $this->vars['files'];
        $this->assertEqual(1, count($files));
    }

}
