<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramParticipantsController', 'Controller');

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
        ClassRegistry::config(array('ds' => 'test'));
        
        $this->dropData();
    }


    protected function dropData()
    {
        $this->instanciateParticipantModel();
        $this->Participants->Participant->deleteAll(true, false);
    }


    protected function instanciateParticipantModel() 
    {
        $options = array('database' => $this->programData[0]['Program']['database']);
        
        $this->Participants->Participant = new Participant($options);
    }


    public function tearDown() 
    {
        
        $this->dropData();
        
        unset($this->Participants);

        parent::tearDown();
    }


    public function mock_program_access()
    {
        $Participants = $this->generate('ProgramParticipants', array(
            'components' => array(
                'Acl' => array('check'),
                'Session' => array('read')
            ),
            'models' => array(
                'Program' => array('find', 'count'),
                'Group' => array()
            ),
        ));
        
        $Participants->Acl
            ->expects($this->any())
            ->method('check')
            ->will($this->returnValue('true'));
        
        $Participants->Program
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->programData));
            
        $Participants->Session
            ->expects($this->any())
            ->method('read')
            ->will($this->onConsecutiveCalls(
                '4', 
                '2',
                $this->programData[0]['Program']['database'],
                $this->programData[0]['Program']['name']
                ));
     

    }


    /**
    * Test methods
    *
    */
    public function testImport_csv() 
    {

        $this->mock_program_access();

        $this->testAction("/testurl/participants/import", array(
            'method' => 'post',
            'data' => array(
                'Import'=> array(
                    'file' => array(
                        'error' => 0,
                        'tmp_name' => TESTS . 'files/wellformattedparticipants.csv',
                        'name' => 'wellformattedparticipants.csv')))
            ));

        $participantInDatabase = $this->Participants->Participant->find('count');
        $this->assertEquals(2, $participantInDatabase);
    }


    public function testImport_csv_duplicate() 
    {

        $this->mock_program_access();

        $this->instanciateParticipantModel();
        $this->Participants->Participant->create();
        $this->Participants->Participant->save(array(
            'phone' => '256712747841',
            'name' => 'Gerald'
            ));


        $this->testAction("/testurl/participants/import", array(
            'method' => 'post',
            'data' => array(
                'Import'=> array(
                    'file' => array(
                        'error' => 0,
                        'tmp_name' => TESTS . 'files/wellformattedparticipants.csv',
                        'name' => 'wellformattedparticipants.csv')))
            ));

        $participantInDatabase = $this->Participants->Participant->find('count');
        $this->assertEquals(2, $participantInDatabase);

        
        $this->assertEquals('256788601462,"Olivier Vernin" insert ok', $this->vars['entries'][1]);
        $this->assertEquals('256712747841,"Gerald Ankunda" duplicated phone line 3', $this->vars['entries'][2]);

    }
    
    
    public function testImport_xls_duplicate() 
    {
        $this->mock_program_access();

        $this->instanciateParticipantModel();
        $this->Participants->Participant->create();
        $this->Participants->Participant->save(array(
            'phone' => '256712747841',
            'name' => 'Gerald'
            ));


        $this->testAction("/testurl/participants/import", array(
            'method' => 'post',
            'data' => array(
                'Import'=> array(
                    'file' => array(
                        'error' => 0,
                        'tmp_name' => TESTS . 'files/wellformattedparticipants.xls',
                        'name' => 'wellformattedparticipants.xls')))
            ));

        $participantInDatabase = $this->Participants->Participant->find('count');
        $this->assertEquals(2, $participantInDatabase);

        
        $this->assertEquals('256788601462,Olivier Vernin insert ok', $this->vars['entries'][2]);
        $this->assertEquals('256712747841,Gerald Ankunda duplicated phone line 3', $this->vars['entries'][3]);

    }


    public function testImport_xls() 
    {
        $this->mock_program_access();

        $this->testAction("/testurl/participants/import", array(
            'method' => 'post',
            'data' => array(
                'Import'=> array(
                    'file' => array(
                        'error' => 0,
                        'tmp_name' => TESTS . 'files/wellformattedparticipants.xls',
                        'name' => 'wellformattedparticipants.xls')))
            ));

        $participantInDatabase = $this->Participants->Participant->find('count');
        $this->assertEquals(2, $participantInDatabase);
    }
    
    
    public function testCheckPhoneNumber() 
    {
        $phoneNumber    = '+712 747.841';
        $newPhoneNumber = $this->Participants->checkPhoneNumber($phoneNumber);
        $this->assertEquals('712747841', $newPhoneNumber);
        //echo $newPhoneNumber."<br />";
        
        $phoneNumber    = '0774521459';
        $newPhoneNumber = $this->Participants->checkPhoneNumber($phoneNumber);
        //$this->assertTrue($phoneNumber == $newPhoneNumber);        
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


}
