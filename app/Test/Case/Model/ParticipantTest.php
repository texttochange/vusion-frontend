    <?php 
App::uses('Participant', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');
App::uses('ScriptMaker', 'Lib');
App::uses('MongodbSource', 'Mongodb.MongodbSource');

class ParticipantTestCase extends CakeTestCase
{
    
    public function setUp()
    {
        parent::setUp();

        $option               = array('database'=>'testdbprogram');
        $this->Participant    = new Participant($option);
        $this->ProgramSetting = new ProgramSetting($option);
        $this->Dialogue       = new Dialogue($option);
        
        $this->Maker = new ScriptMaker();

        $this->dropData();
    }


    public function tearDown()
    {

        $this->dropData();
        unset($this->Participant);
        parent::tearDown();
    }


    public function dropData()
    {
        $this->Participant->deleteAll(true, false);
        $this->ProgramSetting->deleteAll(true, false);
        $this->Dialogue->deleteAll(true, false);
    }


    public function testSave_createData()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $participant = array(
            'phone' => '+788601461',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual($savedParticipant['Participant']['model-version'], '3');  
        $this->assertRegExp('/^[0-9a-fA-F]{32}/', $savedParticipant['Participant']['session-id']);
        $this->assertRegExp('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/', $savedParticipant['Participant']['last-optin-date']);
        $this->assertEqual($savedParticipant['Participant']['last-optout-date'], null);    
        $this->assertTrue(is_array( $savedParticipant['Participant']['tags']));
        $this->assertTrue(is_array( $savedParticipant['Participant']['enrolled']));
        $this->assertTrue(is_array($savedParticipant['Participant']['profile']));
    }


    public function testSave_clearPhone()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        //1st assertion phone is already in the correct format
        $participant = array(
            'phone' => '+788601462',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual('+788601462', $savedParticipant['Participant']['phone']);
        //$this->assertTrue(isset($savedParticipant['Participant']['session-id']));

        //2nd assertion phone is a number
        $participant = array(
            'phone' => 788601463,
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual("+788601463", $savedParticipant['Participant']['phone']);

        //Phone with letter O instead of 0 digit is NOT SAVED
        $participant = array(
            'phone' => 'OO7886O1464',
            );
        $this->Participant->create();
        $this->assertFalse($this->Participant->save($participant));

        //The double 00 are replace by a +
        $participant = array(
            'phone' => '00788601465',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual("+788601465", $savedParticipant['Participant']['phone']);

        //The single 0 is replace by a +
        $participant = array(
            'phone' => '0788601466',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual("+788601466", $savedParticipant['Participant']['phone']);
        
         //The phone is trimmed 
        $participant = array(
            'phone' => ' 0788601467 ',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual("+788601467", $savedParticipant['Participant']['phone']);
    }
    
    
    public function testSave_auto_enrollment()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        
        $dialogue = $this->Maker->getOneDialogue();
        $dialogue['Dialogue']['auto-enrollment'] = 'all';
        
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeActive($savedDialogue['Dialogue']['_id']);

        $participant = array(
            'phone' => ' 07 ',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);

        $this->assertEqual(
            $savedParticipant['Participant']['enrolled'][0]['dialogue-id'],
            $savedDialogue['Dialogue']['dialogue-id']
        );
        $this->assertRegExp(
            '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/',
            $savedParticipant['Participant']['enrolled'][0]['date-time']);
        
    }
    
    
    public function testAutoEnrollDialogue()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        
        $participant = array(
            'phone' => '+7',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);

        $this->Participant->autoEnrollDialogue('01');
        
        $enrolledParticipant = $this->Participant->find('first', array(
            'conditions' => $participant));

        $this->assertEqual(
            $enrolledParticipant['Participant']['enrolled'][0]['dialogue-id'],
            '01'
            );
    }
    
    
    public function testEditParticipantEnroll_notEnrolled_Ok()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        
        $dialogue = $this->Maker->getOneDialogue();
        
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeActive($savedDialogue['Dialogue']['_id']);
        
        $participant = array(
            'phone' => '+7',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual($savedParticipant['Participant']['enrolled'],array());
        
        $savedParticipant['Participant']['enrolled'][0] = $savedDialogue['Dialogue']['dialogue-id'];

        $this->Participant->id = $savedParticipant['Participant']['_id']."";
        $resavedParticipant = $this->Participant->save($savedParticipant);
        
        $enrolledParticipant = $this->Participant->find('first', array(
            'conditions' => $participant));
        
        $this->assertEqual(
            $enrolledParticipant['Participant']['enrolled'][0]['dialogue-id'],
            $savedDialogue['Dialogue']['dialogue-id']
            );
        $this->assertEqual($this->Participant->find('count'), 1);
    }
    
 
    public function testEditParticipantEnroll_alreadyEnrolled_date_unchanged()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
                      
        $dialogue = $this->Maker->getOneDialogue();        
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeActive($savedDialogue['Dialogue']['_id']);        
        
        $otherDialogue = $this->Maker->getOneDialogue();
        $otherSavedDialogue = $this->Dialogue->saveDialogue($otherDialogue);
        $this->Dialogue->makeActive($otherSavedDialogue['Dialogue']['_id']);
        
        $programNow = $this->ProgramSetting->getProgramTimeNow();
        
        $participant = array(
            'phone' => '+7',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);        
        
        $savedParticipant['Participant']['enrolled'][0]['dialogue-id'] = $savedDialogue['Dialogue']['dialogue-id'];
        $savedParticipant['Participant']['enrolled'][0]['date-time'] = '2012-12-12T18:30:00';
        
        $this->Participant->id = $savedParticipant['Participant']['_id']."";
        $savedAgainParticipant = $this->Participant->save($savedParticipant);
        
        $savedAgainParticipant['Participant']['enrolled'][0] = $savedDialogue['Dialogue']['dialogue-id'];
        $savedAgainParticipant['Participant']['enrolled'][1] = $otherSavedDialogue['Dialogue']['dialogue-id'];
        
        $this->Participant->id = $savedAgainParticipant['Participant']['_id']."";
        $resavedParticipant = $this->Participant->save($savedAgainParticipant);
        
        $enrolledParticipant = $this->Participant->find('first', array(
            'conditions' => $participant));
        
        $this->assertEqual(
            $enrolledParticipant['Participant']['enrolled'][0]['dialogue-id'],
            $savedDialogue['Dialogue']['dialogue-id']
            );
        $this->assertEqual(
            $enrolledParticipant['Participant']['enrolled'][0]['date-time'],
            '2012-12-12T18:30:00'
            );
        $this->assertEqual(
            $enrolledParticipant['Participant']['enrolled'][1]['dialogue-id'],
            $otherSavedDialogue['Dialogue']['dialogue-id']
            );
        $this->assertEqual(
            $enrolledParticipant['Participant']['enrolled'][1]['date-time'],
            $programNow->format("Y-m-d\TH:i:s")
            );
        $this->assertEqual(2, count($enrolledParticipant['Participant']['enrolled']));
    }

    public function testGetDistinctTagsAndLabels()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant_08 = array(
            'phone' => '08',
            'tags' => array('geek', 'cool'),
            'profile' => array(
                array('label'=>'city',
                    'value'=> 'kampala',
                    'raw'=> null),
                array('label'=>'gender',
                    'value'=> 'Male',
                    'raw'=> null),
                ));
        $this->Participant->create();
        $this->Participant->save($participant_08);

        $participant_09 = array(
            'phone' => '09',
            'tags' => array('geek', 'another tag'),
            'profile' => array(
                array('label'=>'city',
                    'value'=> 'jinja',
                    'raw'=> 'live in jinja'),
                array('label'=>'gender',
                    'value'=> 'Male',
                    'raw'=> 'gender M'),
                )
            );

        $this->Participant->create();
        $this->Participant->save($participant_09);

        $results = $this->Participant->getDistinctTagsAndLabels();
        $this->assertEqual(array('cool', 'geek', 'another tag', 'city:jinja', 'city:kampala', 'gender:Male' ), $results);
        
    }
    
    
    public function testReset()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        
        $participant = array(
            'phone' => '08',
            'tags' => array('geek', 'cool'),
            'profile' => array(
                array('label'=>'city',
                    'value'=> 'kampala',
                    'raw'=> null),
                array('label'=>'gender',
                    'value'=> 'Male',
                    'raw'=> null),
                ));
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        
        $resetParticipant =$this->Participant->reset($savedParticipant['Participant']);
        
        $this->assertNotEqual($resetParticipant['session-id'], null);
        $this->assertEqual($resetParticipant['tags'], array());
        $this->assertEqual($resetParticipant['profile'], array());
    }


}
