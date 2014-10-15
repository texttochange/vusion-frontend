<?php
App::uses('ProgramUnattachedMessagesController', 'Controller');
App::uses('Schedule', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('History', 'Model');
App::uses('Participant', 'Model');
App::uses('PredefinedMessage', 'Model');

/**
* TestProgramUnattachedMessagesController *
*/
class TestProgramUnattachedMessagesController extends ProgramUnattachedMessagesController
{
    
    public $autoRender = false;
    
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
    
}

/**
* ProgramUnattachedMessagesController Test Case
*
*/
class ProgramUnattachedMessagesControllerTestCase extends ControllerTestCase
{
    var $programData = array(
        0 => array( 
            'Program' => array(
                'name' => 'Test Name',
                'url' => 'testurl',
                'timezone' => 'utc',
                'database' => 'testdbprogram',
                'status' => 'running'
                )
            ));
    
    
    public function setUp() 
    {
        Configure::write("mongo_db",$this->programData[0]['Program']['database']);
        Configure::write("testurl_db",$this->programData[0]['Program']['database']);
        parent::setUp();
        
        $this->ProgramUnattachedMessages = new TestProgramUnattachedMessagesController();
        $this->instanciateModels();
        $this->dropData();
    }
    
    
    protected function instanciateModels() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);    
        $this->UnattachedMessage = new UnattachedMessage($options);
        $this->Schedule          = new Schedule($options);
        $this->ProgramSetting    = new ProgramSetting($options);
        $this->History           = new History($options);
        $this->Participant       = new Participant($options);
        $this->PredefinedMessage = new PredefinedMessage($options);
    }	
    
    
    protected function dropData()
    {
        $this->UnattachedMessage->deleteAll(true, false);
        $this->Schedule->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
        $this->History->deleteAll(true, false);
        $this->Participant->deleteAll(true, false);
        $this->PredefinedMessage->deleteAll(true, false);
    }
    
    
    public function tearDown() 
    {
        $this->dropData();
        
        unset($this->ProgramUnattachedMessages);
        
        parent::tearDown();
    }
    
    
    public function mock_program_access()
    {
        $unattachedMessages = $this->generate(
            'ProgramUnattachedMessages', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth' => array()
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array(),
                    'User' => array('find')
                    ),
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_notifyUpdateBackendWorkerUnattachedMessage',
                    '_notifyUpdateBackendWorkerParticipant'
                    )
                )
            );
        
        $unattachedMessages->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $unattachedMessages->Program
        ->expects($this->once())
        ->method('find')
        ->will($this->returnValue($this->programData));
        
        $unattachedMessages->Session
        ->expects($this->any())
        ->method('read')
        ->will(
            $this->returnValue(
                $this->programData[0]['Program']['database']
                )
            );
        
        return $unattachedMessages;
        
    }
    
    /**
    * Test methods
    *
    */ 
    public function testIndex()
    {
        $userGerald = array(
            'User' => array(
                'id' => 1,
                'username' => 'gerald',
                'password' => 'geraldpassword',
                'email' => 'gerald@here.com',
                'group_id' => 1,
                'created' => '2012-01-24 15:34:07',
                'modified' => '2012-01-24 15:34:07'
                ));
        
        $unattachedMessages = $this->mock_program_access();
        
        $unattachedMessages->User
        ->expects($this->any())
        ->method('find')
        ->will($this->returnValue($userGerald));
        
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow');
        //in case it's midnight
        $date->modify("+4 hour");
        $this->UnattachedMessage->create();
        $this->UnattachedMessage->save(array(
            'name' => 'my message',
            'send-to-type' => 'all',
            'content' => 'Hello!!!!',
            'type-schedule' => 'fixed-time',
            'fixed-time' => $date->format('d/m/Y H:i'),
            'created-by' => 1
            ));
        
        $this->testAction("/testurl/programUnattachedMessages/index");
        
        $this->assertEquals(1, count($this->vars['unattachedMessages']));	
    }

    
    public function testAdd()
    {
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $regexId = $this->matchesRegularExpression('/^.{24}$/');
        
        $unattachedMessages = $this->mock_program_access();
        $unattachedMessages->Auth
        ->staticExpects($this->once())
        ->method('user')
        ->will($this->returnValue(array(
            'id' => '2',
            'group_id' => '2')));
        
        $unattachedMessages
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorkerUnattachedMessage')
        ->with('testurl', $regexId)
        ->will($this->returnValue(true));
        
        $date = new DateTime('tomorrow');    
        $unattachedMessage = array(
            'UnattachedMessage' => array(
                'name' => 'my message',
                'send-to-type' => 'all',
                'content' => 'Hello!!!!',
                'type-schedule' => 'immediately'
                )
            );
        
        $this->testAction("/testurl/programUnattachedMessages/add", array(
            'method' => 'post',
            'data' => $unattachedMessage
            )
            );

        $this->assertEquals(1, $this->UnattachedMessage->find('count'));
        $unattachedMessageDB = $this->UnattachedMessage->find('all');
        
        $this->assertTrue(isset($this->vars['savedUnattachedMessage']['UnattachedMessage']));

        $this->assertTrue(in_array('created-by', array_keys($unattachedMessageDB[0]['UnattachedMessage'])));
        $this->assertEquals(2, $unattachedMessageDB[0]['UnattachedMessage']['created-by']);   
    }
    
    
    public function testAdd_importParticipantFromFile()
    {
        $regexId = $this->matchesRegularExpression('/^.{24}$/');
        $regexPhone = $this->matchesRegularExpression('/^\+[0-9]+$/');
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        
        $unattachedMessages = $this->mock_program_access();
        $unattachedMessages->Auth
        ->staticExpects($this->once())
        ->method('user')
        ->will($this->returnValue(array(
            'id' => '2',
            'group_id' => '2')));
        
        $unattachedMessages
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorkerUnattachedMessage')
        ->with('testurl', $regexId)
        ->will($this->returnValue(true));
        
        $unattachedMessages
        ->expects($this->exactly(2))
        ->method('_notifyUpdateBackendWorkerParticipant')
        ->with('testurl', $regexPhone)
        ->will($this->returnValue(true));
        
        $unattachedMessages->Session
        ->expects($this->once())
        ->method('setFlash')
        ->with('The message has been saved. To be send to 2 participants, 2 have been imported and 0 failed to be imported.');
        
        $date = new DateTime('tomorrow');    
        $unattachedMessage = array(
            'UnattachedMessage' => array(
                'name' => 'my message',
                'send-to-type' => 'phone',
                'content' => 'Hello!!!!',
                'type-schedule' => 'immediately',
                'file' => array(
                    'error' => 0,
                    'tmp_name' => TESTS . 'files/well_formatted_participants.csv',
                    'name' => 'well_formatted_participants.csv'
                    )
                )
            );
        
        $this->testAction("/testurl/programUnattachedMessages/add", array(
            'method' => 'POST',
            'data' => $unattachedMessage
            )
            );
        
        $this->assertFileNotExist(WWW_ROOT . 'files/programs/testurl/well_formatted_participants.csv');
        $this->assertEquals(1, $this->UnattachedMessage->find('count'));
        $unattachedMessage = $this->UnattachedMessage->find('first');
        $this->assertEquals(
            array("+256788601462", "+256712747841"),
            $unattachedMessage['UnattachedMessage']['send-to-phone']);
        $this->assertEquals(2, $this->Participant->find('count'));
    }

    public function testAdd_importParticipantFromList()
    {
        $regexId = $this->matchesRegularExpression('/^.{24}$/');
        $regexPhone = $this->matchesRegularExpression('/^\+[0-9]+$/');
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        
        $unattachedMessages = $this->mock_program_access();
        $unattachedMessages->Auth
        ->staticExpects($this->once())
        ->method('user')
        ->will($this->returnValue(array(
            'id' => '2',
            'group_id' => '2')));
        
        $unattachedMessages
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorkerUnattachedMessage')
        ->with('testurl', $regexId)
        ->will($this->returnValue(true));
        
        $unattachedMessages
        ->expects($this->exactly(2))
        ->method('_notifyUpdateBackendWorkerParticipant')
        ->with('testurl', $regexPhone)
        ->will($this->returnValue(true));
        
        $unattachedMessages->Session
        ->expects($this->once())
        ->method('setFlash')
        ->with('The message has been saved. To be send to 2 participants, 2 have been imported and 0 failed to be imported.');
        
        $date = new DateTime('tomorrow');    
        $unattachedMessage = array(
            'UnattachedMessage' => array(
                'name' => 'my message',
                'send-to-type' => 'phone',
                'content' => 'Hello!!!!',
                'type-schedule' => 'immediately',
                'send-to-phone' => array('256788601462','+256712747841'))
            );
        
        $this->testAction("/testurl/programUnattachedMessages/add", array(
            'method' => 'POST',
            'data' => $unattachedMessage
            )
            );
        
        $this->assertFileNotExist(WWW_ROOT . 'files/programs/testurl/well_formatted_participants.csv');
        $this->assertEquals(1, $this->UnattachedMessage->find('count'));
        $unattachedMessage = $this->UnattachedMessage->find('first');
        $this->assertEquals(
            array("+256788601462", "+256712747841"),
            $unattachedMessage['UnattachedMessage']['send-to-phone']);
        $this->assertEquals(2, $this->Participant->find('count'));
    }    

    
    public function testEdit()
    { 
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        
        $unattachedMessages = $this->mock_program_access();
        
        $date = new DateTime('tomorrow');  
        //in case it's midnight
        $date->modify("+3 hour");
        $unattachedMessage = array(
            'UnattachedMessage' => array(
                'name' => 'test',
                'send-to-type' => 'all',
                'content' => 'Hello!!!!',
                'type-schedule' => 'fixed-time',
                'fixed-time' => $date->format('d/m/Y H:i'),
                'created-by' => 1
                )
            );
        $this->UnattachedMessage->create();
        $data = $this->UnattachedMessage->save($unattachedMessage);
        
        $unattachedMessages
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorkerUnattachedMessage')
        ->with('testurl', $this->UnattachedMessage->id)
        ->will($this->returnValue(true));
        
        $this->testAction("/testurl/programUnattachedMessages/edit/".$data['UnattachedMessage']['_id'],
            array(
                'method' => 'POST',
                'data' => array(
                    'UnattachedMessage' => array(
                        'name' => 'test',
                        'send-to-type' => 'all',
                        'content' => 'Bye!!!!',
                        'type-schedule' => 'fixed-time',
                        'fixed-time' => $date->format('d/m/Y H:i')
                        )
                    )
                )
            );
        $this->UnattachedMessage->id = $data['UnattachedMessage']['_id']."";
        $unattachedMessage = $this->UnattachedMessage->read(); 
        $this->assertEquals(
            'Bye!!!!',
            $unattachedMessage['UnattachedMessage']['content']
            );            
    }


    public function testEdit_typeScheduleToNone()
    { 
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        
        $unattachedMessages = $this->mock_program_access();

        $unattachedMessage = array(
            'UnattachedMessage' => array(
                'name' => 'test',
                'send-to-type' => 'all',
                'content' => 'Hello!!!!',
                'type-schedule' => 'fixed-time',
                'fixed-time' => '3013-04-12T11:00:00',
                'created-by' => 1
                )
            );
        $this->UnattachedMessage->create();
        $savedUnattachedMessage = $this->UnattachedMessage->save($unattachedMessage);
        
        $schedule =array(
            'object-type' => 'unattach-schedule',
            'unattach-id' => $savedUnattachedMessage['UnattachedMessage']['_id'],
            'date-time' => '3013-04-12T11:00:00');
        $this->Schedule->create('unattach-schedule');
        $this->Schedule->save($schedule);

        $this->testAction("/testurl/programUnattachedMessages/edit/".$savedUnattachedMessage['UnattachedMessage']['_id'],
            array(
                'method' => 'POST',
                'data' => array(
                    'UnattachedMessage' => array(
                        'name' => 'test',
                        'send-to-type' => 'all',
                        'content' => 'Hello!!!!',
                        'type-schedule' => 'none'))));
        $this->assertEquals(
            $this->Schedule->count(),
            0);            
    }
    

    public function testDelete()
    {
        $this->mock_program_access();
        
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');
        
        $date = new DateTime('tomorrow');
        //in case it's midnight
        $date->modify("+3 hour");
        $unattachedMessage = array(
            'UnattachedMessage' => array(
                'name' => 'test',
                'send-to-type' => 'all',
                'content' => 'Hello!!!!',
                'type-schedule' => 'fixed-time',
                'fixed-time' => $date->format('d/m/Y H:i'),
                'created-by' => 1
                )
            );
        $this->UnattachedMessage->create();
        $data = $this->UnattachedMessage->save($unattachedMessage);
        
        $scheduleToBeDeleted= array(
            'Schedule' => array(
                'unattach-id' => $data['UnattachedMessage']['_id'],
                )
            );
        
        $this->Schedule->create('unattach-schedule');
        $this->Schedule->save($scheduleToBeDeleted);
        
        $this->testAction("/testurl/programUnattachedMessages/delete/".$data['UnattachedMessage']['_id']);
        
        $this->assertEquals(
            0,
            $this->UnattachedMessage->find('count')
            );
        $this->assertEquals(
            0,
            $this->Schedule->find('count')
            );
        
    }
    
    
    public function testIndex_unattachedMessageStatus()
    {     
        $this->mock_program_access();
        
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');        
        $unattachedMessage = array(            
            'name' => 'test',
            'send-to-type' => 'all',
            'content' => 'Hello!!!!',
            'type-schedule' => 'immediately',
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage= $this->UnattachedMessage->save($unattachedMessage);        
        $history = array(
            'object-type' => 'unattach-history',
            'participant-phone' => '788601462',
            'timestamp' => '2012-03-06T11:06:34 ',
            'message-content' => 'FEEL nothing',
            'message-direction' => 'outgoing', 
            'message-status' => 'pending',
            'unattach-id' => $savedUnattachedMessage['UnattachedMessage']['_id'].''
            );       
        $this->History->create('unattach-history');
        $saveHistoryStatus = $this->History->save($history);  
        
        $this->testAction("/testurl/programUnattachedMessages/index");        
        $this->assertEquals(1, count($this->vars['unattachedMessages']));
    }
    
    
    public function testIndex_scheduleUnattachedMessageStatus()
    {     
        $this->mock_program_access();
        
        $this->ProgramSetting->saveProgramSetting('timezone','Africa/Kampala');        
        $unattachedMessage = array(            
            'name' => 'test',
            'send-to-type' => 'all',
            'content' => 'Hello!!!!',
            'type-schedule' => 'fixed-time',
            'fixed-time' => '12/04/3013 11:00',
            'created-by' => 1
            );
        $this->UnattachedMessage->create("unattached-message");
        $savedUnattachedMessage= $this->UnattachedMessage->save($unattachedMessage); 
        
        $schedule =array(
            'object-type' => 'unattach-schedule',
            'unattach-id' => $savedUnattachedMessage['UnattachedMessage']['_id'],
            'date-time' => '3013-04-12T11:00:00',           
            );       
        $this->Schedule->create('unattach-schedule');
        $saveScheduleCount = $this->Schedule->save($schedule);  
        
        $this->testAction("/testurl/programUnattachedMessages/index");
        
        $this->assertEquals(1, count($this->vars['unattachedMessages'])); 
    }


}
