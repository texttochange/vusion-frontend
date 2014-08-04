<?php
App::uses('ProgramParticipantsController', 'Controller');
App::uses('Schedule', 'Model');
App::uses('ScriptMaker', 'Lib');
App::uses('Dialogue', 'Model');

class TestProgramParticipantsController extends ProgramParticipantsController
{
    
    public $autoRender = false;
    
    
    public function redirect($url, $status = null, $exit = true)
    {
        $this->redirectUrl = $url;
    }
    
}


class ProgramParticipantsControllerTestCase extends ControllerTestCase
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
        parent::setUp();
        
        $this->Participants = new TestProgramParticipantsController();
        
        $options = array('database' => $this->programData[0]['Program']['database']);   
        $this->Participant    = new Participant($options);
        $this->Schedule       = new Schedule($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->History        = new History($options);
        $this->Dialogue       = new Dialogue($options);
        
        $this->dropData();
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $this->Maker = new ScriptMaker();
        
    }
    
    
    protected function dropData()
    {
        $this->Participant->deleteAll(true, false);
        $this->Schedule->deleteAll(true,false);
        $this->ProgramSetting->deleteAll(true,false);
        $this->History->deleteAll(true, false);
        $this->Dialogue->deleteAll(true, false);
    }
    
    
    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Participants);
        
        parent::tearDown();
    }
    
    
    public function mockProgramAccess_withoutSession()
    {
        $participants = $this->generate(
            'ProgramParticipants', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth',
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_notifyUpdateBackendWorker',
                    'render',
                    )
                )
            );
        
        $participants->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue('true'));
        
        $participants->Program
        ->expects($this->once())
        ->method('find')
        ->will($this->returnValue($this->programData));
        
        return $participants;
        
    }
    
    
    public function mockProgramAccess()
    {
        $participants = $this->mockProgramAccess_withoutSession();
        
        $participants->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->returnValue($this->programData[0]['Program']['database']));
        
        return $participants;
    }
    
    
    public function testAdd()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256788601462')
        ->will($this->returnValue(true));
        
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');    
        
        $participant = $this->Maker->getParticipant();
        $this->testAction(
            "/testurl/participants/add", 
            array(
                'method' => 'post',
                'data' => $participant
                )
            );
        
    }
    
    
    public function testImport_csv_no_settings() 
    {
        $this->mockProgramAccess();
        
        $this->testAction(
            "/testurl/participants/import", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'file' => array(
                            'error' => 0,
                            'tmp_name' => TESTS . 'files/well_formatted_participants.csv',
                            'name' => 'well_formatted_participants.csv'
                            )
                        )
                    )
                )
            );
        
        $participants = $this->Participant->find('all');
        $this->assertEquals(0, count($participants));
    }
    
    
    public function testImport_csv() 
    {
        $regexPhone = $this->matchesRegularExpression('/^\+[0-9]{12}$/');
        
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->any())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', $regexPhone)
        ->will($this->returnValue(true));
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        
        $this->testAction(
            "/testurl/participants/import", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'file' => array(
                            'error' => 0,
                            'tmp_name' => TESTS . 'files/well_formatted_participants.csv',
                            'name' => 'well_formatted_participants.csv'
                            )
                        )
                    )
                )
            );
        
        $this->assertFileNotExist(WWW_ROOT . 'files/programs/testurl/well_formatted_participants.csv');
        $this->assertEquals(2, count($this->vars['report']));
    }
    
    
    public function testImport_csv_duplicate() 
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256788601462')
        ->will($this->returnValue(true));
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');    
        
        $this->instanciateParticipantModel();
        $this->Participant->create();
        $this->Participant->save(
            array(
                'phone' => '+256712747841',
                'name' => 'Gerald'
                )
            );
        
        $this->testAction(
            "/testurl/participants/import", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'file' => array(
                            'error' => 0,
                            'tmp_name' => TESTS . 'files/well_formatted_participants.csv',
                            'name' => 'well_formatted_participants.csv'
                            )
                        )
                    )
                )
            );
        
        $this->assertFileNotExist(WWW_ROOT . 'files/programs/testurl/well_formatted_participants.csv');
        $this->assertEquals(
            'Insert ok',
            $this->vars['report'][0]['message'][0]
            );
        $this->assertEquals(
            'This phone number already exists in the participant list.',
            $this->vars['report'][1]['message'][0]
            );
    }
    
    
    public function testImport_noLabelTwoColumns_fail() 
    {
        $regexPhone = $this->matchesRegularExpression('/^\+[0-9]{12}$/');
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->any())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', $regexPhone)
        ->will($this->returnValue(true));        
        $participants->Session
        ->expects($this->once())
        ->method('setFlash')
        ->with("The file cannot be imported. The first line should be label names, the first label must be 'phone'.");
        
        $this->testAction(
            "/testUrl/participantsController/import", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'file' => array(
                            'error' => 0,
                            'tmp_name' => TESTS . 'files/no_label_two_columns.csv',
                            'name' => 'no_label_two_columns.csv'
                            )
                        )
                    )
                )
            );
        
        $participants = $this->Participant->find('all');
        $this->assertEquals(0, count($participants));
        $this->assertFileNotExist(WWW_ROOT . 'files/programs/testurl/no_label_two_columns.csv');
    }
    
    
    
    public function testImport_xls_duplicate() 
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256788601462')
        ->will($this->returnValue(true));
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        
        $this->instanciateParticipantModel();
        $this->Participant->create();
        $this->Participant->save(
            array(
                'phone' => '256712747841',
                'name' => 'Gerald'
                )
            );
        
        $this->testAction(
            "/testurl/participants/import", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'file' => array(
                            'error' => 0,
                            'tmp_name' => TESTS . 'files/well_formatted_participants.xls',
                            'name' => 'well_formatted_participants.xls'
                            )
                        )
                    )
                )
            );
        
        $this->assertFileNotExist(WWW_ROOT . 'files/programs/testurl/wellformattedparticipants.xls');
        
        $this->assertEquals(
            'Insert ok',
            $this->vars['report'][0]['message'][0]
            );
        $this->assertEquals(
            'This phone number already exists in the participant list.',
            $this->vars['report'][1]['message'][0]
            );
    }
    
    
    public function testImport_xls_wellFromated() 
    {
        $regexPhone = $this->matchesRegularExpression('/^\+[0-9]{12}$/');
        
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->any())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', $regexPhone)
        ->will($this->returnValue(true));
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        
        $this->testAction(
            "/testurl/participants/import", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'file' => array(
                            'error' => 0,
                            'tmp_name' => TESTS . 'files/well_formatted_participants.xls',
                            'name' => 'well_formatted_participants.xls'
                            )
                        )
                    )
                )
            );
        
        $this->assertFileNotExist(WWW_ROOT . 'files/programs/testurl/wellformattedparticipants.xls');
        $this->assertEquals(2, count($this->vars['report']));
    }
    
    
    public function testDeleteParticipant()
    {
        $this->mockProgramAccess();
        
        $participant = array(
            'phone' => '06'
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $scheduleToBeDeleted = array(
            'participant-phone' => '+6',
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToBeDeleted);
        
        $scheduleToStay = array(
            'participant-phone' => '+7',
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToStay);
        
        $this->testAction("/testurl/programParticipants/delete/".$participantDB['Participant']['_id']);
        
        $this->assertEquals(
            0,
            $this->Participant->find('count')
            );
        $this->assertEquals(
            1,
            $this->Schedule->find('count')
            );
    }
    
    
    public function testDeleteParticipant_withHistory()
    {
        $this->mockProgramAccess();
        
        $participant = array(
            'phone' => '06');
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $historyToBeDeleted = array(
            'participant-phone' => '+6',
            'message-direction' => 'incoming');
        
        $this->History->create('dialogue-history');
        $this->History->save($historyToBeDeleted);
        
        $this->testAction("/testurl/programParticipants/delete/".$participantDB['Participant']['_id']."?include=history");
        
        $this->assertEquals(
            0,
            $this->Participant->find('count'));
        $this->assertEquals(
            0,
            $this->History->find('count'));
    }
    
    
    public function testMassDeleteFilteredParticipant()
    {
        $this->mockProgramAccess();
        
        $participant = array(
            'phone' => '+6');
        
        $this->Participant->create();
        $this->Participant->save($participant);
        
        $scheduleToBeDeleted = array(
            'participant-phone' => '+6',
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToBeDeleted);
        
        $participant = array(
            'phone' => '+7');
        
        $this->Participant->create();
        $this->Participant->save($participant);
        
        $participant = array(
            'phone' => '+8');
        
        $this->Participant->create();
        $this->Participant->save($participant);
        
        $scheduleToStay = array(
            'participant-phone' => '+8',
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToStay);
        
        $this->testAction("/testurl/programParticipants/massDelete?filter_operator=all&filter_param[1][1]=phone&filter_param[1][2]=equal-to&filter_param[1][3]=%2B6");
        
        $this->assertEquals(
            2,
            $this->Participant->find('count'));
        $this->assertEquals(
            1,
            $this->Schedule->find('count'));
    }
    
    public function testMassDeleteFilteredParticipant_failMissingFilterParameters()
    {
        $this->mockProgramAccess();
        
        $participant = array(
            'phone' => '+6');
        
        $this->Participant->create();
        $this->Participant->save($participant);
        
        try {
            $this->testAction("/testurl/programParticipants/massDelete?filter_param[1][1]=phone&filter_param[1][2]=equal-to&filter_param[1][3]=%2B6");
            $this->failed('Missing filter operator should rise an exception.');
        } catch (FilterException $e) {
            $this->assertEqual($e->getMessage(), "Filter operator is missing or not allowed.");
        }
        $this->assertEquals(
            1,
            $this->Participant->find('count'));
    }
    
    
    public function testMassDeleteParticipant()
    {
        $this->mockProgramAccess();
        
        $participant = array(
            'phone' => '+6');
        
        $this->Participant->create();
        $this->Participant->save($participant);
        
        $scheduleToBeDeleted = array(
            'participant-phone' => '+6',
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToBeDeleted);
        
        $participant = array(
            'phone' => '+7');
        
        $this->Participant->create();
        $this->Participant->save($participant);
        
        $participant = array(
            'phone' => '+8');
        
        $this->Participant->create();
        $this->Participant->save($participant);
        
        $scheduleToStay = array(
            'participant-phone' => '+8',
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToStay);
        
        $this->testAction("/testurl/programParticipants/massDelete");
        
        $this->assertEquals(
            0,
            $this->Participant->find('count'));
        $this->assertEquals(
            0,
            $this->Schedule->find('count'));
    }
    
    
    public function testEditParticipant()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+7')
        ->will($this->returnValue(true));
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $scheduleToBeDeleted = array(
            'Schedule' => array(
                'participant-phone' => '+256712747841',
                )
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToBeDeleted);
        
        $this->testAction(
            "/testurl/programParticipants/edit/".$participantDB['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '07'
                        )
                    )
                )
            );
        
        $this->assertEquals(1, $this->Participant->find('count'));
        $this->assertEquals(0, $this->Schedule->find('count'));
    }
    
    
    public function testEditParticipantProfile()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256712747841')
        ->will($this->returnValue(true));
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $editedParticipant = $this->testAction(
            "/testurl/programParticipants/edit/".$participantDB['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '+256712747841',
                        'profile' => 'gender:male',
                        )
                    )
                )
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEqual($participantFromDb['Participant']['profile'][0]['label'], 'gender');
        $this->assertEqual($participantFromDb['Participant']['profile'][0]['value'], 'male');
    }


    public function testEditParticipantProfile_without_id()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256712747841')
        ->will($this->returnValue(true));
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $editedParticipant = $this->testAction(
            "/testurl/programParticipants/edit",
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '+256712747841',
                        'profile' => 'gender:male',
                        )
                    )
                )
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEqual($participantFromDb['Participant']['profile'][0]['label'], 'gender');
        $this->assertEqual($participantFromDb['Participant']['profile'][0]['value'], 'male');
    }

    
    public function testEditParticipantProfile_specialCharacters_fail()
    {
        $participants = $this->mockProgramAccess();
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $this->testAction(
            "/testurl/programParticipants/edit/".$participantDB['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '+256712747841',
                        'profile' => 'food:+=[/',
                        )
                    )
                )
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEqual($participantFromDb['Participant']['profile'], array());
    }
    
    
    public function testEditParticipantProfile_only_label_fail()
    {
        $participants = $this->mockProgramAccess();
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $this->testAction(
            "/testurl/programParticipants/edit/".$participantDB['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '+256712747841',
                        'profile' => 'food',
                        )
                    )
                )
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEqual($participantFromDb['Participant']['profile'], array());
        
        $participants = $this->mockProgramAccess();
        $this->testAction(
            "/testurl/programParticipants/edit/".$participantDB['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '+256712747841',
                        'profile' => 'town:',
                        )
                    )
                )
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEqual($participantFromDb['Participant']['profile'], array());
    }
    
    
    public function testEditParticipantTags()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256712747841')
        ->will($this->returnValue(true));
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $this->testAction(
            "/testurl/programParticipants/edit/".$participantDB['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '+256712747841',
                        'tags' => 'me,you, him',
                        )
                    )
                )
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEqual($participantFromDb['Participant']['tags'][0], 'me');
        $this->assertEqual($participantFromDb['Participant']['tags'][1], 'you');
        $this->assertEqual($participantFromDb['Participant']['tags'][2], 'him');
    }
    
   
    public function testEditParticipantTags_notAlphaNumeric_fail()
    {
        $participants = $this->mockProgramAccess();
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $this->testAction(
            "/testurl/programParticipants/edit/".$participantDB['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '+256712747841',
                        'tags' => 'me,you, |"him',
                        )
                    )
                )
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEqual($participantFromDb['Participant']['tags'], array());
    }
    
    
    public function testEditParticipantEnrolls()
    {
        $participants = $this->mockProgramAccess();
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        $participantDB['Participant']['enrolled'][0] = array(
            'dialogue-id'=>'abc123',
            'date-time'=>'2012-12-12T15:15:00'
            );
        
        $this->Participant->id = $participantDB['Participant']['_id']."";
        $savedParticipant = $this->Participant->save($participantDB);
        
        $this->assertEqual(1,count($savedParticipant['Participant']['enrolled']));
        
        $this->testAction(
            "/testurl/programParticipants/edit/".$participantDB['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '+256712747841',
                        'enrolled' => array(
                            '0'=>'abc123',
                            '1'=>'def456'),
                        )
                    )
                )
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual(2,count($participantFromDb['Participant']['enrolled']));
    }
    
    
    public function testEditParticipantEnrolls_deleteSchedule()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256712747841')
        ->will($this->returnValue(true));
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        $participantDB['Participant']['enrolled'][0] = array(
            'dialogue-id'=>'abc123',
            'date-time'=>'2012-12-12T15:15:00'
            );
        $participantDB['Participant']['enrolled'][1] = array(
            'dialogue-id'=>'def456',
            'date-time'=>'2012-12-12T15:15:00'
            );
        
        $this->Participant->id = $participantDB['Participant']['_id']."";
        $savedParticipant = $this->Participant->save($participantDB);
        
        $scheduleToBeDeleted = array(
            'Schedule' => array(
                'participant-phone' => '+256712747841',
                'dialogue-id' => 'def456',
                )
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToBeDeleted);
        
        $scheduleToStay = array(
            'Schedule' => array(
                'participant-phone' => '+256712747841',
                'dialogue-id' => 'abc123',
                )
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToStay);
        
        $this->testAction(
            "/testurl/programParticipants/edit/".$participantDB['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'Participant' => array(
                        'phone' => '+256712747841',
                        'enrolled' => array(
                            '0'=>'abc123'
                            ),
                        )
                    )
                )
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual(1,count($participantFromDb['Participant']['enrolled']));
        $this->assertEquals(0, $this->Schedule->find('count'));
    }
    
    
    public function testView_displayScheduled()
    {
        $participants = $this->mockProgramAccess();
        
        $participant = array(
            'Participant' => array(
                'phone' => '06',
                )
            );
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $scheduleToBeDisplayed = array(
            'Schedule' => array(
                'participant-phone' => '+6',
                )
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleToBeDisplayed);
        
        $this->testAction(
            "/testurl/programParticipants/view/".$participantDB['Participant']['_id']
            );
        
        $this->assertEquals(
            1,
            $this->Participant->find('count')
            );
        $this->assertEquals(
            1,
            $this->Schedule->find('count')
            );
    }
    
    
    public function testIndex_filter_fail()
    {
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=phone&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=%2B2");
        $this->assertEquals(0, count($this->vars['participants']));
    }

    
    public function testIndex_filter()
    {
        $this->Participant->create();
        $savedParticipant = $this->Participant->save(array(
            'phone' => '+26',
            'session-id' => '1',
            'last-optin-date' => '2012-12-01T18:30:10',
            'enrolled' => array(),
            'tags' => array('Geek'),
            'profile' => array(array(
                'label'=> 'gender',
                'value' => 'male',
                'raw' => null))
            ));
        
        $savedParticipant['Participant']['enrolled'][0] = array(
            'dialogue-id' => '1',
            'date-time'=> '2012-12-01T18:30:10');
        $this->Participant->id = $savedParticipant['Participant']['_id']."";
        $this->Participant->save($savedParticipant);
        
        $this->Participant->create();
        $savedParticipant = $this->Participant->save(array(
            'phone' => '+27',
            'session-id' => null,
            'last-optin-date' => null,
            'enrolled' => array(array(
                'dialogue-id' => '1',
                'date-time'=> '2012-12-01T18:30:10')),
            'tags' => array('Geek'),
            'profile' => array(array(
                'label'=> 'gender',
                'value' => 'male',
                'raw' => null))
            ));
        
        $savedParticipant['Participant']['session-id'] = null;
        $savedParticipant['Participant']['last-optin-date'] = null;
        $savedParticipant['Participant']['enrolled'][0] = array(
            'dialogue-id' => '1',
            'date-time'=> '2012-12-01T18:30:10');
        $this->Participant->id = $savedParticipant['Participant']['_id']."";
        $this->Participant->save($savedParticipant);
        
        $this->Participant->create();
        $this->Participant->save(array(
            'phone' => '+28',
            'session-id' => '2',
            'last-optin-date' => '2012-12-01T18:30:10',
            'enrolled' => array(),
            'tags' => array('Super Geek'),
            'profile' => array()
            ));
        
        $this->Participant->create();
        $savedParticipant = $this->Participant->save(array(
            'phone' => '+29',
            'session-id' => '3',
            'last-optin-date' => '2012-12-02T18:30:10',
            'enrolled' => array(),
            'tags' => array(),
            'profile' => array(array(
                'label'=> 'gender',
                'value' => 'female',
                'raw' => null))
            ));
        $savedParticipant['Participant']['last-optin-date'] = '2012-12-02T18:30:10';
        $this->Participant->id = $savedParticipant['Participant']['_id']."";
        $this->Participant->save($savedParticipant);
      
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=phone&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=%2B2");
        $this->assertEquals(4, count($this->vars['participants']));
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=phone&filter_param%5B1%5D%5B2%5D=start-with&filter_param%5B1%5D%5B3%5D=%2B27");
        $this->assertEquals(1, count($this->vars['participants']));
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=optin&filter_param%5B1%5D%5B2%5D=now");
        $this->assertEquals(3, count($this->vars['participants']));

        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=optin&filter_param%5B1%5D%5B2%5D=date-from&filter_param%5B1%5D%5B3%5D=02%2F12%2F2012");
        $this->assertEquals(1, count($this->vars['participants']));
       
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=optin&filter_param%5B1%5D%5B2%5D=date-to&filter_param%5B1%5D%5B3%5D=02%2F12%2F2012");
        $this->assertEquals(2, count($this->vars['participants']));
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=optout&filter_param%5B1%5D%5B2%5D=now");
        $this->assertEquals(1, count($this->vars['participants']));
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=enrolled&filter_param%5B1%5D%5B2%5D=in&filter_param%5B1%5D%5B3%5D=1");
        $this->assertEquals(2, count($this->vars['participants']));
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=tagged&filter_param%5B1%5D%5B2%5D=with&filter_param%5B1%5D%5B3%5D=Geek");
        $this->assertEquals(2, count($this->vars['participants']));
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/index?filter_operator=all&filter_param%5B1%5D%5B1%5D=labelled&filter_param%5B1%5D%5B2%5D=with&filter_param%5B1%5D%5B3%5D=gender:female");
        $this->assertEquals(1, count($this->vars['participants']));        
    }
    
    
    public function testGetFilterParameterIndex()
    {
        $exprectedTags = array('Geek', 'Hipster');
        $exprectedLabels = array('gender:male');
        
        $this->Participant->create();
        $this->Participant->save(array(
            'phone' => '+26',
            'session-id' => '1',
            'last-optin-date' => '2012-12-01T18:30:10',
            'enrolled' => array(),
            'tags' => array('Geek'),
            'profile' => array(array(
                'label'=> 'gender',
                'value' => 'male',
                'raw' => null))
            ));
        
        $this->Participant->create();
        $this->Participant->save(array(
            'phone' => '+27',
            'session-id' => null,
            'last-optin-date' => null,
            'enrolled' => array(),
            'tags' => array('Hipster'),
            'profile' => array()
            ));
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/getFilterParameterOptions.json?parameter=tag&filter_operator=all&filter_param%5B1%5D%5B1%5D=tagged&filter_param%5B1%5D%5B2%5D=with&filter_param%5B1%5D%5B3%5D=Geek");
        $this->assertEquals($exprectedTags, $this->vars['results']);
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/getFilterParameterOptions.json?parameter=label&filter_operator=all&filter_param%5B1%5D%5B1%5D=labelled&filter_param%5B1%5D%5B2%5D=with&filter_param%5B1%5D%5B3%5D=gender:male");
        $this->assertEquals($exprectedLabels, $this->vars['results']);
    }
    
    
    public function testExport()
    {
        
        $participants = $this->mockProgramAccess_withoutSession();
        
        $participants->Session
        ->expects($this->any())
        ->method('read')
        ->will($this->onConsecutiveCalls(
            '4', 
            '2',
            $this->programData[0]['Program']['database'],
            $this->programData[0]['Program']['name'],
            'Africa/Kampala',
            'testdbprogram',
            'name1', //?
            'name2', //?
            $this->programData[0]['Program']['name'] //only for export test to get program name
            ));
        
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                'tags' => array('geek', 'cool'),
                )
            );
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256788601462',
                'profile' => array( 
                    array( 'label' => 'name', 
                        'value' => 'olivier', 
                        'raw' => null))
                )
            );
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        
        $this->testAction("/testurl/programParticipants/export");
        
        $this->assertTrue(isset($this->vars['ajaxResult']['fileName']));
        $this->assertFileEquals(
            TESTS . 'files/exported_participants.csv',
            WWW_ROOT . 'files/programs/testurl/' . $this->vars['ajaxResult']['fileName']);
    }
    
    
    public function testReset()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256712747841')
        ->will($this->returnValue(true));
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256712747841',
                )
            );        
        
        $this->Participant->create();
        $participantDB = $this->Participant->save($participant);
        $participantDB['Participant']['enrolled'][0] = array(
            'dialogue-id'=>'abc123',
            'date-time'=>'2012-12-12T15:15:00'
            );
        $participantDB['Participant']['enrolled'][1] = array(
            'dialogue-id'=>'def456',
            'date-time'=>'2012-12-12T15:15:00'
            );
        $participantDB['Participant']['last-optin-date'] = '2012-12-02T18:30:10';
        
        $this->Participant->id = $participantDB['Participant']['_id']."";
        $savedParticipant = $this->Participant->save($participantDB);
        
        $scheduleOne = array(
            'Schedule' => array(
                'participant-phone' => '+256712747841',
                'dialogue-id' => 'def456',
                )
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleOne);
        
        $scheduleTwo = array(
            'Schedule' => array(
                'participant-phone' => '+256712747841',
                'dialogue-id' => 'abc123',
                )
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($scheduleTwo);
        
        $this->testAction(
            "/testurl/programParticipants/reset/".$participantDB['Participant']['_id']
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual(0,count($participantFromDb['Participant']['enrolled']));
        $this->assertEquals(0, $this->Schedule->find('count'));
        $this->assertNotEqual($participantFromDb['Participant']['last-optin-date'], '2012-12-02T18:30:10');
    }
    
    
    public function testOptout()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+7')
        ->will($this->returnValue(true));
        
        $participant = array(
            'phone' => ' 07 ',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        
        $schedule = array(
            'Schedule' => array(
                'participant-phone' => '+7',
                'dialogue-id' => 'abc123',
                )
            );
        
        $this->Schedule->create('dialogue-schedule');
        $this->Schedule->save($schedule);
        
        $programNow = $this->ProgramSetting->getProgramTimeNow();
        
        $this->testAction(
            "/testurl/programParticipants/optout/".$savedParticipant['Participant']['_id']
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertEqual(
            $participantFromDb['Participant']['session-id'],
            null
            );
        $this->assertRegex(
            "/^".$programNow->format("Y-m-d\TH:i:")."\d\d$/",
            $participantFromDb['Participant']['last-optout-date']
            );
        $this->assertEquals(0, $this->Schedule->find('count'));
    }
    
    
    public function testOptin()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+7')
        ->will($this->returnValue(true));
        
        $participant = array(
            'phone' => ' 07 ',
            'session-id' => null
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        
        $dialogue = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['auto-enrollment'] = 'all';
        
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeActive($savedDialogue['Dialogue']['_id']);
        
        $this->testAction(
            "/testurl/programParticipants/optin/".$savedParticipant['Participant']['_id']
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertNotEqual(
            $participantFromDb['Participant']['session-id'],
            null
            );
        $this->assertEqual(
            $participantFromDb['Participant']['last-optout-date'],
            null
            );
        $this->assertEqual(
            $participantFromDb['Participant']['enrolled'][0]['dialogue-id'],
            $savedDialogue['Dialogue']['dialogue-id']
            );
    }
    
    public function testMassTagFilteredParticipant_ok()
    {
        $this->mockProgramAccess();
        
        $participant_01 = array(
            'phone' => '+6', 
            );
        $this->Participant->create();
        $this->Participant->save($participant_01);        
        
        $participant_02 = array(
            'phone' => '+7',            
            );
        $this->Participant->create();
        $this->Participant->save($participant_02);        
        
        $this->testAction(("/testurl/programParticipants/masstag?"
            .'filter_operator=all'
            .'&filter_param[1][1]=phone'
            .'&filter_param[1][2]=equal-to'
            .'&filter_param[1][3]=%2B6'
            .'&tag=test'),
            array('method' => 'get')); 
        
        $conditions = array(
            'conditions' => array(               
                'tags' => 'test'));        
        $participants = $this->Participant->find('all', $conditions);      
        $this->assertEqual(1, count($participants));
        $this->assetEqual('+6', $participants[0]['Participant']['phone']);
        
    }
    
    
    public function testMassUntagFilteredParticipant_ok()
    {
        $this->mockProgramAccess();
        
        $participant_01 = array(
            'phone' => '+6',
            'tags' => array('test','hi')
            );
        $this->Participant->create();
        $this->Participant->save($participant_01);        
        
        $participant_02 = array(
            'phone' => '+7',
            'tags' => array('test2','hi','home')
            );
        $this->Participant->create();
        $this->Participant->save($participant_02); 
        
        $this->testAction(("/testurl/programParticipants/massuntag?"
            .'filter_operator=all'
            .'&filter_param[1][1]=tagged'
            .'&filter_param[1][2]=with'
            .'&filter_param[1][3]=test'
            .'&tag=test'),
            array('method' => 'get'));
        
        $condition1 = array(
        	'conditions' => array(
        		'phone' => '+6'));
        $Participant1 = $this->Participant->find('all', $condition1);
        $this->assetEqual(array('hi'), $Participant1[0]['Participant']['tags']);
        $condition2 = array(
        	'conditions' => array(
        		'phone' => '+7'));
        $Participant2 = $this->Participant->find('all', $condition2);
        $this->assertEqual(array('test2', 'hi', 'home'), $Participant2[0]['Participant']['tags']);
    }


    public function testPaginationCount()
    {
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/paginationCount.json");
        $this->assertEqual($this->vars['ajaxResult']['paginationCount'], 0);
    }

    
}
