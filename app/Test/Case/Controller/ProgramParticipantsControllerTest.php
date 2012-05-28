<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramParticipantsController', 'Controller');
App::uses('Schedule', 'Model');

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
        $this->instanciateParticipantModel();
        $this->instanciateScheduleModel();
        $this->dropData();
    }


    protected function dropData()
    {
        $this->instanciateParticipantModel();
        $this->Participants->Participant->deleteAll(true, false);
        $this->Participants->Schedule->deleteAll(true,false);
    }


    protected function instanciateParticipantModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);   

        $this->Participants->Participant = new Participant($options);
    }

    protected function instanciateScheduleModel()
    {
        $options = array('database' => $this->programData[0]['Program']['database']);   

        $this->Participants->Schedule = new Schedule($options);
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


    /**
    * Test methods
    *
    */
    public function testImport_csv() 
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

        $participantInDatabase = $this->Participants->Participant->find('count');
        $this->assertEquals(2, $participantInDatabase);
    }


    public function testImport_csv_duplicate() 
    {

        $participants = $this->mock_program_access();
        $participants
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->will($this->returnValue(true));
        

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
            '+256788601462,"Olivier Vernin" Insert ok',
            $this->vars['entries'][1]
            );
        $this->assertEquals(
            '+256712747841,"Gerald Ankunda" This phone number already exists in the participant list. line 3',
            $this->vars['entries'][2]
            );
    }
    
    
    public function testImport_xls_duplicate() 
    {
        $participants = $this->mock_program_access();
        $participants
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->will($this->returnValue(true));

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
            '256788601462,Olivier Vernin Insert ok',
            $this->vars['entries'][2]
            );
        $this->assertEquals(
            '256712747841,Gerald Ankunda This phone number already exists in the participant list. line 3',
            $this->vars['entries'][3]
            );
    }


    public function testImport_xls() 
    {
        $participants = $this->mock_program_access();
        $participants
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->will($this->returnValue(true));
        
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
    }
    
    
    public function testCheckPhoneNumber() 
    {
        $phoneNumber    = '+712 747.841';
        $newPhoneNumber = $this->Participants->checkPhoneNumber($phoneNumber);
        $this->assertEquals('712747841', $newPhoneNumber);
        
        $phoneNumber    = '0774521459';
        $newPhoneNumber = $this->Participants->checkPhoneNumber($phoneNumber);
        $this->assertFalse(strpos($newPhoneNumber, '0'));
        
        $phoneNumber    ='(0)782123123';
        $newPhoneNumber = $this->Participants->checkPhoneNumber($phoneNumber);
        $this->assertEquals('782123123', $newPhoneNumber);
        
        $phoneNumber    ='782123023';
        $newPhoneNumber = $this->Participants->checkPhoneNumber($phoneNumber);
        $this->assertEquals('782123023', $newPhoneNumber);
        
        $phoneNumber    ='782123044 ';
        $newPhoneNumber = $this->Participants->checkPhoneNumber($phoneNumber);
        $this->assertEquals('782123044', $newPhoneNumber);
    }


    public function testDeleteParticipant()
    {
        $this->mock_program_access();
        
        $participant = array(
            'Participant' => array(
                'phone' => '06',
                'name' => 'oliv',
                )
            );

        $this->Participants->Participant->create();
        $participantDB = $this->Participants->Participant->save($participant);

        $scheduleToBeDeleted = array(
            'Schedule' => array(
                'participant-phone' => '+6',
                )
            );

        $this->Participants->Schedule->create();
        $this->Participants->Schedule->save($scheduleToBeDeleted);

        $scheduleToStay = array(
            'Schedule' => array(
                'participant-phone' => '+7',
                )
            );

        $this->Participants->Schedule->create();
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


    public function testEditParticipant()
    {
        $participants = $this->mock_program_access();
        $participants
            ->expects($this->once())
            ->method('_notifyUpdateBackendWorker')
            ->will($this->returnValue(true));
        
        $participant = array(
            'Participant' => array(
                'phone' => '06',
             )
        );

        $this->Participants->Participant->create();
        $participantDB = $this->Participants->Participant->save($participant);

        $scheduleToBeDeleted = array(
            'Schedule' => array(
                'participant-phone' => '+6',
                )
            );

        $this->Participants->Schedule->create();
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


}
