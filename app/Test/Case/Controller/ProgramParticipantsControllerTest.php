<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramParticipantsController', 'Controller');
App::uses('Schedule', 'Model');
App::uses('ScriptMaker', 'Lib');

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
                'database' => 'testdbprogram'
                )
        ));


    public function setUp()
    {
        parent::setUp();

        $this->Participants = new TestProgramParticipantsController();

        $options = array('database' => $this->programData[0]['Program']['database']);   
        $this->Participant = new Participant($options);
        $this->Schedule = new Schedule($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->History = new History($options);

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
    }

    public function tearDown()
    {
        
        $this->dropData();
        
        unset($this->Participants);

        parent::tearDown();
    }


    public function mock_program_access()
    {
        $participants = $this->generate(
            'ProgramParticipants', array(
                'components' => array(
                    'Acl' => array('check'),
                    'Session' => array('read')
                    ),
                'models' => array(
                    'Program' => array('find', 'count'),
                    'Group' => array()
                    ),
                'methods' => array(
                    '_notifyUpdateBackendWorker'
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
            
        $participants->Session
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
     
        return $participants;
    }

/*
    public function testAdd()
    {
        $participants = $this->mock_program_access();
        $participants
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->with('testurl', '+256788601462')
            ->will($this->returnValue(true));

         
        $this->Participants->ProgramSetting->saveProgramSetting('shortcode', '8282');    

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
        $this->mock_program_access();

        $this->testAction(
            "/testurl/participants/import", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'file' => array(
                            'error' => 0,
                            'tmp_name' => TESTS . 'files/wellformattedparticipants.csv',
                            'name' => 'wellformattedparticipants.csv'
                            )
                        )
                    )
                )
            );

        $participants = $this->Participants->Participant->find('all');
        $this->assertEquals(0, count($participants));
    }
    
    
    public function testImport_csv() 
    {
        $this->mock_program_access();
        
        $this->Participants->ProgramSetting->saveProgramSetting('shortcode', '8282');

        $this->testAction(
            "/testurl/participants/import", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'file' => array(
                            'error' => 0,
                            'tmp_name' => TESTS . 'files/wellformattedparticipants.csv',
                            'name' => 'wellformattedparticipants.csv'
                            )
                        )
                    )
                )
            );

        $participants = $this->Participants->Participant->find('all');
        $this->assertEquals(2, count($participants));
        $this->assertEquals($participants[0]['Participant']['profile'][0]['label'], 'Name');
        $this->assertEquals($participants[0]['Participant']['profile'][0]['value'], 'Olivier Vernin');
        $this->assertEquals($participants[0]['Participant']['profile'][1]['label'], 'DoB');
        $this->assertEquals($participants[0]['Participant']['profile'][1]['value'], '21st of July');
        $this->assertEquals($participants[1]['Participant']['profile'][0]['value'], 'Gerald Ankunda');
        $this->assertEquals($participants[1]['Participant']['profile'][1]['value'], '30th of March');
    }


    public function testImport_csv_duplicate() 
    {

        $participants = $this->mock_program_access();
        $participants
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->with('testurl', '+256788601462')
            ->will($this->returnValue(true));
        
        $this->Participants->ProgramSetting->saveProgramSetting('shortcode', '8282');    
            
        $this->instanciateParticipantModel();
        $this->Participants->Participant->create();
        $this->Participants->Participant->save(
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
                            'tmp_name' => TESTS . 'files/wellformattedparticipants.csv',
                            'name' => 'wellformattedparticipants.csv'
                            )
                        )
                    )
                )
            );

        $participantInDatabase = $this->Participants->Participant->find('count');
        $this->assertEquals(2, $participantInDatabase);

        
        $this->assertEquals(
            '+256788601462, Insert ok',
            $this->vars['entries'][1]
            );
        $this->assertEquals(
            '256712747841, This phone number already exists in the participant list. line 3',
            $this->vars['entries'][2]
            );
    }
    
    
    public function testImport_xls_duplicate() 
    {
        $participants = $this->mock_program_access();
        $participants
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->with('testurl', '+256788601462')
            ->will($this->returnValue(true));
            
        $this->Participants->ProgramSetting->saveProgramSetting('shortcode', '8282');

        $this->instanciateParticipantModel();
        $this->Participants->Participant->create();
        $this->Participants->Participant->save(
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
                            'tmp_name' => TESTS . 'files/wellformattedparticipants.xls',
                            'name' => 'wellformattedparticipants.xls'
                            )
                        )
                    )
                )
            );

        $participantInDatabase = $this->Participants->Participant->find('count');

        $this->assertEquals(2, $participantInDatabase);

        
        $this->assertEquals(
            '+256788601462, Insert ok',
            $this->vars['entries'][2]
            );
        $this->assertEquals(
            '256712747841, This phone number already exists in the participant list. line 3',
            $this->vars['entries'][3]
            );
    }


    public function testImport_xls() 
    {
        $regexPhone = $this->matchesRegularExpression('/^\+[0-9]{12}$/');

        $participants = $this->mock_program_access();
        $participants
            ->expects($this->any())
            ->method('_notifyUpdateBackendWorker')
            ->with('testurl', $regexPhone)
            ->will($this->returnValue(true));
            
        $this->Participants->ProgramSetting->saveProgramSetting('shortcode', '8282');
        
        $this->testAction(
            "/testurl/participants/import", 
            array(
                'method' => 'post',
                'data' => array(
                    'Import'=> array(
                        'file' => array(
                            'error' => 0,
                            'tmp_name' => TESTS . 'files/wellformattedparticipants.xls',
                            'name' => 'wellformattedparticipants.xls'
                            )
                        )
                    )
                )
            );

        $participants = $this->Participants->Participant->find('all');
        $this->assertEquals(2, count($participants));
        $this->assertEquals($participants[0]['Participant']['profile'][0]['label'], 'Name');
        $this->assertEquals($participants[0]['Participant']['profile'][0]['value'], 'Olivier Vernin');
    }
    

    public function testDeleteParticipant()
    {
        $this->mock_program_access();
        
        $participant = array(
            'phone' => '06'
            );

        $this->Participants->Participant->create();
        $participantDB = $this->Participants->Participant->save($participant);

        $scheduleToBeDeleted = array(
            'participant-phone' => '+6',
            );

        $this->Participants->Schedule->create('dialogue-schedule');
        $this->Participants->Schedule->save($scheduleToBeDeleted);

        $scheduleToStay = array(
            'participant-phone' => '+7',
            );

        $this->Participants->Schedule->create('dialogue-schedule');
        $this->Participants->Schedule->save($scheduleToStay);

        $this->testAction("/testurl/programParticipants/delete/".$participantDB['Participant']['_id']);
        
        $this->assertEquals(
            0,
            $this->Participants->Participant->find('count')
            );
        $this->assertEquals(
            1,
            $this->Participants->Schedule->find('count')
            );
    }
*/

    public function testDeleteParticipant_withHistory()
    {
        $this->mock_program_access();
        
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

/*
    public function testEditParticipant()
    {
        $participants = $this->mock_program_access();
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

        $this->Participants->Participant->create();
        $participantDB = $this->Participants->Participant->save($participant);

        $scheduleToBeDeleted = array(
            'Schedule' => array(
                'participant-phone' => '+256712747841',
                )
            );

        $this->Participants->Schedule->create('dialogue-schedule');
        $this->Participants->Schedule->save($scheduleToBeDeleted);

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
        
        $this->assertEquals(
            1,
            $this->Participants->Participant->find('count')
            );
        $this->assertEquals(
            0,
            $this->Participants->Schedule->find('count')
            );
    }
    
    
    public function testView_displayScheduled()
    {
        $participants = $this->mock_program_access();
                
        $participant = array(
            'Participant' => array(
                'phone' => '06',
             )
        );

        $this->Participants->Participant->create();
        $participantDB = $this->Participants->Participant->save($participant);

        $scheduleToBeDisplayed = array(
            'Schedule' => array(
                'participant-phone' => '+6',
                )
            );

        $this->Participants->Schedule->create('dialogue-schedule');
        $this->Participants->Schedule->save($scheduleToBeDisplayed);

        $this->testAction(
            "/testurl/programParticipants/view/".$participantDB['Participant']['_id']
            );
        
        $this->assertEquals(
            1,
            $this->Participants->Participant->find('count')
            );
        $this->assertEquals(
            1,
            $this->Participants->Schedule->find('count')
            );
    }
*/

}
