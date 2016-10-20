<?php
App::uses('ProgramParticipantsController', 'Controller');
App::uses('Schedule', 'Model');
App::uses('ScriptMaker', 'Lib');
App::uses('TestHelper', 'Lib');
App::uses('Dialogue', 'Model');
App::uses('Participant', 'Model');
App::uses('History', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


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
                'name' => 'Test Name?good/for|testing &me%',
                'url' => 'testurl',
                'timezone' => 'utc',
                'database' => 'testdbprogram',
                'status' => 'running'
                )
            ));
    
    
    public function setUp()
    {
        parent::setUp();
        
        $this->ProgramParticipants = new TestProgramParticipantsController();
        $dbName = $this->programData[0]['Program']['database'];
        $this->setModel('Participant', $dbName);
        $this->setModel('Schedule', $dbName);
        $this->setModel('ProgramSetting', $dbName);
        $this->setModel('History', $dbName);
        $this->setModel('Dialogue', $dbName);
        $this->Export = ClassRegistry::init('Export');
        
        $this->dropData();
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $this->Maker = new ScriptMaker();
    }
    
    protected function setModel($classModel, $dbName) {
        $this->{$classModel} = ProgramSpecificMongoModel::init(
            $classModel, $dbName, true);
    }
    
    
    protected function dropData()
    {
        $this->Participant->deleteAll(true, false);
        $this->Schedule->deleteAll(true,false);
        $this->ProgramSetting->deleteAll(true,false);
        $this->History->deleteAll(true, false);
        $this->Dialogue->deleteAll(true, false);
        $this->Export->deleteAll(true, false);
        TestHelper::deleteAllProgramFiles('testurl');
    }
    
    
    public function tearDown()
    {
        $this->dropData();
        unset($this->ProgramParticipants);
        parent::tearDown();
    }
    
    
    public function mockProgramAccess_withoutSession()
    {
        $participants = $this->generate(
            'ProgramParticipants', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read', 'setFlash'),
                    'Auth' => array('loggedIn', 'startup'),
                    'Mash' => array('importParticipants'),
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                'methods' => array(
                    '_instanciateVumiRabbitMQ',
                    '_notifyUpdateBackendWorker',
                    '_notifyBackendMassTag',
                    '_notifyBackendMassUntag',
                    '_notifyBackendRunActions',
                    '_notifyBackendExport',
                    '_sendSimulateMoVumiRabbitMQ',
                    'render',
                    )
                )
            );
        
        $participants->Acl
        ->expects($this->any())
        ->method('check')
        ->will($this->returnValue(true));
        
        $participants->Auth
        ->expects($this->any())
        ->method('loggedIn')
        ->will($this->returnValue(true));
        
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
    
    /*
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
    
    
    public function testAdd_forceOptin()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256788601462')
        ->will($this->returnValue(true));
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');    
        
        $participant = array('Participant' => array(
            'phone' => '256788601462',
            'last-optout-date' => '2015-01-01T10:10:00'));
        $this->Participant->create();
        $this->Participant->save($participant);
        
        //Add the force_input parameter
        $participant['Participant']['force-optin'] = 'true';
        $this->testAction(
            "/testurl/participants/add", 
            array(
                'method' => 'post',
                'data' => $participant,
                )
            );
        
    }
    
    
    public function testImportFile_csv_no_settings() 
    {
        $this->mockProgramAccess();
        
        $this->testAction(
            "/testurl/participants/importFile", 
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
    
    
    public function testImportFile_csv() 
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
            "/testurl/participants/importFile", 
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
        
        $this->assertFileNotExists(WWW_ROOT . 'files/programs/testurl/well_formatted_participants.csv');
        $this->assertEquals(2, count($this->vars['report']));
    }
    
    
    public function testImportFile_csv_duplicate() 
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256788601462')
        ->will($this->returnValue(true));
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');    
        
        $this->Participant->create();
        $this->Participant->save(
            array(
                'phone' => '+256712747841',
                'name' => 'Gerald'
                )
            );
        
        $this->testAction(
            "/testurl/participants/importFile", 
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
        
        $this->assertFileNotExists(WWW_ROOT . 'files/programs/testurl/well_formatted_participants.csv');
        $this->assertEquals(
            'Insert ok',
            $this->vars['report'][0]['message'][0]
            );
        $this->assertEquals(
            'This phone number already exists in the participant list.',
            $this->vars['report'][1]['message'][0]
            );
    }
    
    
    public function testImportFile_noLabelTwoColumns_fail() 
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
            "/testUrl/participantsController/importFile", 
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
        $this->assertFileNotExists(WWW_ROOT . 'files/programs/testurl/no_label_two_columns.csv');
    }
    
    
    
    public function testImportFile_xls_duplicate() 
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256788601462')
        ->will($this->returnValue(true));
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        
        $this->Participant->create();
        $this->Participant->save(
            array(
                'phone' => '256712747841',
                'name' => 'Gerald'
                )
            );
        
        $this->testAction(
            "/testurl/participants/importFile", 
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
        
        $this->assertFileNotExists(WWW_ROOT . 'files/programs/testurl/wellformattedparticipants.xls');
        
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
            "/testurl/participants/importFile", 
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
        
        $this->assertFileNotExists(WWW_ROOT . 'files/programs/testurl/wellformattedparticipants.xls');
        $this->assertEquals(2, count($this->vars['report']));
    }
    
    
    public function testImportMash_ok() 
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyUpdateBackendWorker')
        ->with('testurl', '+256788601462')
        ->will($this->returnValue(true));
        
        $participantJson = '[{"phone_number":"256788601462","profile":{"location":{"value":"Mombasa"}}},{"phone_number":"256712747841","profile":{}}]';
        $participantJsonDecoded = json_decode($participantJson);
        $participants->Mash
        ->expects($this->once())
        ->method('importParticipants')
        ->with('UGA')
        ->will($this->returnValue($participantJsonDecoded));
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '256-8282');
        $this->ProgramSetting->saveProgramSetting('international-prefix', '256');    
        
        $this->Participant->create();
        $this->Participant->save(
            array(
                'phone' => '+256712747841',
                'name' => 'Gerald'
                )
            );
        
        $this->testAction(
            "/testurl/participants/importMash", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'country' => 'UGA',
                        'tags' => 'mytag, anothertag')
                    )
                )
            );
        
        $this->assertEquals(
            'Insert ok',
            $this->vars['report'][0]['message'][0]);
        $this->assertEquals(
            'This phone number already exists in the participant list.',
            $this->vars['report'][1]['message'][0]);
        $importedParticipant = $this->Participant->find('first', array('conditions' => array('phone' => '+256788601462')));
        $this->assertEqual(
            array('imported', 'mash',  'mytag', 'anothertag'),
            $importedParticipant['Participant']['tags']);
        
    }
    
    
    public function testImportMash_fail_mash() 
    {
        $participants = $this->mockProgramAccess();
        $participants->Mash
        ->expects($this->once())
        ->method('importParticipants')
        ->with('UGA')
        ->will($this->returnValue(null));
        $participants->Session
        ->expects($this->once())
        ->method('setFlash')
        ->with('The import failed because the Mash server is not responding, please report the issue.');
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '256-8282');
        $this->ProgramSetting->saveProgramSetting('international-prefix', '256');    
        
        $this->testAction(
            "/testurl/participants/importMash", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'country' => 'UGA')
                    )
                )
            );
        
        $this->assertFalse($this->vars['requestSuccess']);
    }
    
    
    public function testImportMash_fail_countryNotAllowed() 
    {
        $participants = $this->mockProgramAccess();
        $participants->Session
        ->expects($this->once())
        ->method('setFlash')
        ->with('Import not allowed of participant from France.');
        
        $this->ProgramSetting->saveProgramSetting('shortcode', '256-8282');
        $this->ProgramSetting->saveProgramSetting('international-prefix', '256');    
        
        $this->testAction(
            "/testurl/participants/importMash", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'country' => 'FRA')
                    )
                )
            );
        
        $this->assertFalse($this->vars['requestSuccess']);
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
            'object-type' => 'dialogue-history',
            'participant-phone' => '+6',
            'message-direction' => 'incoming');
        
        $this->History->create($historyToBeDeleted);
        $this->History->save($historyToBeDeleted);
        
        $this->testAction("/testurl/programParticipants/delete/".$participantDB['Participant']['_id']."?include=history");
        
        $this->assertEquals(
            0,
            $this->Participant->find('count'));
        $this->assertEquals(
            0,
            $this->History->find('count'));
    }
    
    
    public function testDelete_simulatedParticipant_alwaysWithHistory()
    {
        $this->mockProgramAccess();
        
        $participant = array('simulate' => true);
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        
        $historyToBeDeleted = array(
            'object-type' => 'dialogue-history',
            'participant-phone' => $savedParticipant['Participant']['phone'],
            'message-direction' => 'incoming');
        
        $this->History->create($historyToBeDeleted);
        $this->History->save($historyToBeDeleted);
        
        $this->testAction("/testurl/programParticipants/delete/".$savedParticipant['Participant']['_id']);
        
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
            $this->assertEquals($e->getMessage(), "Filter operator is missing.");
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
        $this->assertEquals($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEquals($participantFromDb['Participant']['profile'][0]['label'], 'gender');
        $this->assertEquals($participantFromDb['Participant']['profile'][0]['value'], 'male');
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
        $this->assertEquals($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEquals($participantFromDb['Participant']['profile'][0]['label'], 'gender');
        $this->assertEquals($participantFromDb['Participant']['profile'][0]['value'], 'male');
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
        $this->assertEquals($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEquals($participantFromDb['Participant']['profile'], array());
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
        $this->assertEquals($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEquals($participantFromDb['Participant']['profile'], array());
        
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
        $this->assertEquals($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEquals($participantFromDb['Participant']['profile'], array());
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
        $this->assertEquals($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEquals($participantFromDb['Participant']['tags'][0], 'me');
        $this->assertEquals($participantFromDb['Participant']['tags'][1], 'you');
        $this->assertEquals($participantFromDb['Participant']['tags'][2], 'him');
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
        $this->assertEquals($participantDB['Participant']['_id'],$participantFromDb['Participant']['_id']);
        $this->assertEquals($participantFromDb['Participant']['tags'], array());
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
        
        $this->assertEquals(1,count($savedParticipant['Participant']['enrolled']));
        
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
        $this->assertEquals(2,count($participantFromDb['Participant']['enrolled']));
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
        $this->assertEquals(1,count($participantFromDb['Participant']['enrolled']));
        $this->assertEquals(0, $this->Schedule->find('count'));
    }
    
    
    public function testRunActions_ok()
    {
        $dialogue = $this->Maker->getOneDialogueWithKeyword();
        $this->Dialogue->create();
        $savedDialogue = $this->Dialogue->save($dialogue);
        $this->Dialogue->makeActive();
        
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyBackendRunActions')
        ->with('testurl', array(
            'phone' => '+256111111',
            'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
            'interaction-id' => $savedDialogue['Dialogue']['interactions'][1]['interaction-id'],
            'answer' => 'Good'))
        ->will($this->returnValue(true));
        
        $participant = array(
            'Participant' => array(
                'phone' => '+256111111',
                )
            );
        $this->Participant->create();
        $this->Participant->save($participant);
        
        $this->testAction(
            "/testurl/programParticipants/runActions.json",
            array(
                'method' => 'post',
                'data' => array(
                    'phone' => '0256111111',
                    'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
                    'interaction-id' => $savedDialogue['Dialogue']['interactions'][1]['interaction-id'],
                    'answer' => 'Good',
                    )
                )
            );
        $this->assertTrue($this->vars['requestSuccess']);
    }
    
    
    public function testRunActions_fail_validation()
    {
        $participants = $this->mockProgramAccess();
        $this->testAction(
            "/testurl/programParticipants/runActions.json",
            array(
                'method' => 'post',
                'data' => array(
                    'phone' => '+256111111',
                    'dialogue-id' => '1',
                    'intaction-id' => '1',
                    'answer' => 'Good',
                    )
                )
            );
        
        $this->assertFalse($this->vars['requestSuccess']);
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
    
    
    public function testListParticipants()
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
        
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/listParticipants.json?filter_operator=all&filter_param%5B1%5D%5B1%5D=labelled&filter_param%5B1%5D%5B2%5D=with&filter_param%5B1%5D%5B3%5D=gender:female");
        $this->assertEquals(1, count($this->vars['participants']));
    }*/
    
    
    public function testListSurveyParticipants()
    {
        $this->Participant->create();
        $savedParticipant = $this->Participant->save(array(
            'phone' => '+26',
            'session-id' => '1',
            'last-optin-date' => '2012-12-01T18:30:10',
            'enrolled' => array(),
            'tags' => array('Geek'),
            'profile' => array(
                0 => array(
                    'label'=> 'gender',
                    'value' => 'male',
                    'raw' => null),
                1 => array(
                    'raw' => '6PBRB',
                    'value' => 'PBRB',
                    'label' => '6'
                    ))
            ));
        $this->Participant->create();
        $savedParticipant = $this->Participant->save(array(
            'phone' => '+29',
            'session-id' => '3',
            'last-optin-date' => '2012-12-02T18:30:10',
            'enrolled' => array(),
            'tags' => array('imported', '13', '12', '16', '89', '56'),
            'profile' => array(
                0 => array(
                    'label'=> 'gender',
                    'value' => 'female',
                    'raw' => null),
                1 => array(
                    'raw' => '5tousled',
                    'value' => 'tousled',
                    'label' => 'Answer5'
                    ),
                2 => array(
                    'raw' => null,
                    'value' => '787',
                    'label' => 'id'
                    ),
                3 => array(
                    'raw' => null,
                    'value' => '79988',
                    'label' => 'reporterid'
                    ),
                4 => array(
                    'raw' => '6tousled',
                    'value' => 'tousledty',
                    'label' => 'Answer6'
                    ))
            ));
        $this->Participant->create();
        $savedParticipant = $this->Participant->save(array(
            'phone' => '+20',
            'session-id' => '8',
            'last-optin-date' => '2012-12-02T18:30:10',
            'enrolled' => array(),
            'tags' => array('imported', '134', '123', '164', '839'),
            'profile' => array(
                0 => array(
                    'label'=> 'gender',
                    'value' => 'female',
                    'raw' => null),
                1 => array(
                    'raw' => '5tousled',
                    'value' => 'tousled',
                    'label' => 'Answer5'
                    ),
                2 => array(
                    'raw' => null,
                    'value' => '56767',
                    'label' => 'reporterid'
                    ),
                3 => array(
                    'raw' => '6tousled',
                    'value' => 'tousledty',
                    'label' => 'Answer6'
                    ))
            ));
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/listSurveyParticipants.json?filter_operator=all&filter_param%5B1%5D%5B1%5D=labelled&filter_param%5B1%5D%5B2%5D=with&filter_param%5B1%5D%5B3%5D=gender:female");
        
        $this->assertEquals(9, count($this->vars['participantSurveyProfileList']));
    }
    
    /*
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
        $participants = $this->mockProgramAccess();
        $expectedCondition = array('$or' => array(
            array('simulate' => false),
            array('simulate' => array('$exists' => false))));
        $participants
        ->expects($this->once())
        ->method('_notifyBackendExport')
        ->with(
            $this->matchesRegularExpression('/^[a-f0-9]+$/'))
        ->will($this->returnValue(true));
        
        $this->testAction("/testurl/programParticipants/export");
        
        $this->assertEqual($this->Export->find('count'), 1);
        $export = $this->Export->find('first');
        $this->assertTrue(isset($export['Export']));
        $this->assertContains(
            'Test_Name_good_for_testing_me_participants_', 
            $export['Export']['file-full-name']);
        $this->assertEquals(
            $expectedCondition,
            $export['Export']['conditions']);
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
        $this->assertEquals(0,count($participantFromDb['Participant']['enrolled']));
        $this->assertEquals(0, $this->Schedule->find('count'));
        $this->assertNotEquals($participantFromDb['Participant']['last-optin-date'], '2012-12-02T18:30:10');
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
        $this->assertEquals(
            $participantFromDb['Participant']['session-id'],
            null
            );
        $this->assertRegExp(
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
        $this->Dialogue->makeActive();
        
        $this->testAction(
            "/testurl/programParticipants/optin/".$savedParticipant['Participant']['_id']
            );
        
        $participantFromDb = $this->Participant->find();
        $this->assertNotEquals(
            $participantFromDb['Participant']['session-id'],
            null
            );
        $this->assertEquals(
            $participantFromDb['Participant']['last-optout-date'],
            null
            );
        $this->assertEquals(
            $participantFromDb['Participant']['enrolled'][0]['dialogue-id'],
            $savedDialogue['Dialogue']['dialogue-id']
            );
    }
    
    
    public function testMassTagFilteredParticipant_ok()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyBackendMassTag')
        ->with('testurl', 'test', array('phone' => '+6'))
        ->will($this->returnValue(true));
        
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
        $this->assertEquals(1, count($participants));
        $this->assertEquals('+6', $participants[0]['Participant']['phone']);
        
    }
    
    
    public function testMassUntagFilteredParticipant_ok()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->once())
        ->method('_notifyBackendMassUntag')
        ->with('testurl', 'test')
        ->will($this->returnValue(true));
        
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
        $this->assertEquals(array('hi'), $Participant1[0]['Participant']['tags']);
        $condition2 = array(
        	'conditions' => array(
        		'phone' => '+7'));
        $Participant2 = $this->Participant->find('all', $condition2);
        $this->assertEquals(array('test2', 'hi', 'home'), $Participant2[0]['Participant']['tags']);
    }
    
    
    public function testPaginationCount()
    {
        $this->mockProgramAccess();
        $this->testAction("/testurl/programParticipants/paginationCount.json");
        $this->assertEquals($this->vars['paginationCount'], 0);
    }
    
    
    public function testExported()
    {
        $this->mockProgramAccess();
        $this->Export->create();
        $this->Export->save(array(
            'database' => 'testdbprogram',
            'collection' => 'participants',
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
        $this->assertEqual(2, count($files));
    }
    
    
    public function testTrim_simulateMo()
    {
        $participants = $this->mockProgramAccess();
        $participants
        ->expects($this->any())
        ->method('_sendSimulateMoVumiRabbitMQ')
        ->with('testurl', '#6', 'testing send')
        ->will($this->returnValue(true));
        
        $participant_01 = array(
            'phone' => '#6',
            'simulate' => true
            );
        $this->Participant->create();
        $savedSimulatedParticipant = $this->Participant->save($participant_01); 
        
        $this->testAction(
            "/testurl/programParticipants/simulateMo/".$savedSimulatedParticipant['Participant']['_id'],
            array(
                'method' => 'post',
                'data' => array(
                    'phone' => '#6',
                    'message' => "  testing send\r\n ",
                    )
                )
            );
    }*/
    
}
