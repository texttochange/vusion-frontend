<?php 
App::uses('Participant', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');
App::uses('ScriptMaker', 'Lib');
App::uses('MongodbSource', 'Mongodb.MongodbSource');
App::uses('FilterException', 'Lib');
App::uses('ProgramSpecificMongoModel', 'Model');


class ParticipantTestCase extends CakeTestCase
{
    
    public function setUp()
    {
        parent::setUp();
        $dbName = 'testdbprogram';
        $this->Participant = ProgramSpecificMongoModel::init(
            'Participant', $dbName);
        $this->Dialogue = ProgramSpecificMongoModel::init(
            'Dialogue', $dbName);
        $this->ProgramSetting = ProgramSpecificMongoModel::init(
            'ProgramSetting', $dbName);

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
    
    
    public function testSave_normalParticipantSave()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $participant = array(
            'phone' => '+788601461',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual($savedParticipant['Participant']['model-version'], '5');  
        $this->assertRegExp('/^[0-9a-fA-F]{32}/', $savedParticipant['Participant']['session-id']);
        $this->assertRegExp('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/', $savedParticipant['Participant']['last-optin-date']);
        $this->assertEqual($savedParticipant['Participant']['last-optout-date'], null);
    }
    
    
    public function testSave_simulatedParticipant()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $participant2 = array(
            'simulate' => true
            );
        $this->Participant->create();
        
        $savedParticipant = $this->Participant->save($participant2);
        $this->assertEqual($savedParticipant['Participant']['model-version'], '5');
        $this->assertEqual($savedParticipant['Participant']['phone'], '#1');
        $this->assertRegExp('/^[0-9a-fA-F]{32}/', $savedParticipant['Participant']['session-id']);
        $this->assertRegExp('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/', $savedParticipant['Participant']['last-optin-date']);
        $this->assertEqual($savedParticipant['Participant']['last-optout-date'], null); 
    }
    
    
    public function testSave_normalParticipant_fail()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $participant2 = array(
            'phone' => '#878845555'
            );
        $this->Participant->create();
        
        $savedParticipant = $this->Participant->save($participant2);
        $this->assertEqual($this->Participant->validationErrors['phone'][0],
            "A phone number must begin with a '+' sign and end with a serie of digits such as +335666555.");  
    
    }
    
    
    public function testSave_clearEmpty()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $participant = array(
            'phone' => '+788601461',
            'tags' => array('a tag',''),
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual(
            $savedParticipant['Participant']['tags'],
            array('a tag'));
    }


    public function testSave_forceOptin()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $participant = array('Participant' => array(
            'phone' => '+788601461',
            'last-optout-date' => '2014-01-01T10:10:00'));
        $this->Participant->create();
        $this->Participant->save($participant);

        $participant = array('Participant' => array(
            'phone' => '+788601461',
            'last-optout-date' => '2014-01-01T10:10:00',
            'force-optin' => 'false'));
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertFalse($savedParticipant);

        $participant = array('Participant' => array(
            'phone' => '+788601461',
            'last-optout-date' => '2014-01-01T10:10:00',
            'force-optin' => 'true'));
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertTrue(isset($savedParticipant['Participant']));
    }
    
    
    public function testCleanTags() 
    {
        $tags = ' a tag ';
        $this->assertEquals(
            array('a tag'),
            Participant::cleanTags($tags));

        $tags = ', a tag ,';
        $this->assertEquals(
            array('a tag'),
            Participant::cleanTags($tags));

        $tags = array('', 'a tag ');
        $this->assertEquals(
            array('a tag'),
            Participant::cleanTags($tags));

        $tags = 'sometag,a tag ,';
        $this->assertEquals(
            array('sometag', 'a tag'),
            Participant::cleanTags($tags));
    }
    
    
    public function testCleanProfile() 
    {
        $profile = ' group:1 ';
        $this->assertEquals(
            Participant::cleanProfile($profile),
            array(
                array(
                    'label' => 'group',
                    'value' => '1',
                    'raw' => null)));

        $profile = ', group : 1 ,';
        $this->assertEquals(
            Participant::cleanProfile($profile),
            array(
                array(
                    'label' => 'group',
                    'value' => '1',
                    'raw' => null)));

        $profile = array(
            array(),
            array(
                'label' => 'group ',
                'value' => ' 1'),
            array());
        $this->assertEquals(
            Participant::cleanProfile($profile),
            array(
                array(
                    'label' => 'group',
                    'value' => '1',
                    'raw' => null)));

    }
    
    
    public function testSave_tags_labels_as_string()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $participant = array(
            'phone' => '+788601461',
            'tags' => 'a tag, Another tag1, áéíóúüñ',
            'profile' => 'email:someone@gmail.com, town: kampala, accent: áéíóúüñ',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual(
            $savedParticipant['Participant']['tags'],
            array('a tag', 'Another tag1', 'áéíóúüñ'));

        $this->assertEqual(
            $savedParticipant['Participant']['profile'],
            array(
                array('label'=>'email',
                    'value' => 'someone@gmail.com',
                    'raw' => null),
                array('label' => 'town',
                    'value' => 'kampala',
                    'raw' => null),
                array('label' => 'accent',
                    'value' => 'áéíóúüñ',
                    'raw' => null))
            );
    }
    
    
    public function testSave_noUnique_fail()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant = array(
            'phone' => '+7886014612',
            );
        $this->Participant->create();
        $this->Participant->save($participant);

        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);

        $this->assertFalse($savedParticipant);
        $this->assertEqual(
            'This phone number already exists in the participant list.',
            $this->Participant->validationErrors['phone'][0]);
    }
    

    public function testSave_valiationPhone_fail()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant = array(
            'phone' => '2.5679E+11',
            );
        $this->Participant->create();
        $this->assertFalse($this->Participant->save($participant));

        $this->assertEqual(
            "A phone number must begin with a '+' sign and end with a serie of digits such as +335666555.",
        $this->Participant->validationErrors['phone'][0]);
    }
    
    
    public function testSave_valiationLabel()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant = array(
            'phone' => '25601',
            'profile' => array(
                array(
                    'label' => 'balance',
                    'value' => '16.01'
                    ),
                ),
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertTrue(isset($savedParticipant));
    }
    
    
    public function testSave_valiationLabel_failEmptyValue()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant = array(
            'phone' => '25601',
            'profile' => array(
                array(
                    'label' => 'balance',
                    'value' => null
                    ),
                ),
            );
        $this->Participant->create();
        $this->assertFalse($this->Participant->save($participant));
        $this->assertEqual(
            $this->Participant->validationErrors['profile'][0],
            'The label value cannot be empty.');


        $participant = array(
            'phone' => '25601',
            'profile' => array(
                array(
                    'label' => 'balance',
                    'value' => ''
                    ),
                ),
            );
        $this->Participant->create();
        $this->assertFalse($this->Participant->save($participant));
        $this->assertEqual(
            $this->Participant->validationErrors['profile'][0],
            'The label value cannot be empty.');
    }
    
    
    public function testSave_cleanPhone()
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
    
    
    public function testEditParticipantEnroll_notEnrolled_Ok()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $dialogue      = $this->Maker->getOneDialogue();
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeActive();

        $participant = array(
            'phone' => '+7',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual($savedParticipant['Participant']['enrolled'],array());

        $savedParticipant['Participant']['enrolled'][0] = $savedDialogue['Dialogue']['dialogue-id'];

        $this->Participant->id = $savedParticipant['Participant']['_id']."";
        $resavedParticipant    = $this->Participant->save($savedParticipant);

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

        $dialogue      = $this->Maker->getOneDialogue();
        $savedDialogue = $this->Dialogue->saveDialogue($dialogue);
        $this->Dialogue->makeActive();

        $otherDialogue      = $this->Maker->getOneDialogue();
        $otherSavedDialogue = $this->Dialogue->saveDialogue($otherDialogue);
        $this->Dialogue->makeActive();

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
        $resavedParticipant    = $this->Participant->save($savedAgainParticipant);

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
        $timeDiff = $programNow->diff(new DateTime($enrolledParticipant['Participant']['enrolled'][1]['date-time'])); 
        $this->assertTrue($timeDiff->format('%s') <= 1);
        $this->assertEqual(2, count($enrolledParticipant['Participant']['enrolled']));
    }
    
    
    public function testGetDistinctTagsAndLabels()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $this->assertEqual(array(), $this->Participant->getDistinctTagsAndLabels());

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
    
    
    public function testGetDistinctLabels()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $this->assertEqual(array(), $this->Participant->getDistinctTagsAndLabels());

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

        $results = $this->Participant->getDistinctLabels();
        $this->assertEqual(
            array('city:jinja', 'city:kampala', 'gender:Male' ), 
            $results);

    }
    
    
    public function testGetHeaderExport()
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

        $results = $this->Participant->getExportHeaders();
        $this->assertEqual(
            array(
                'phone', 
                //    'last-optin-date', 
                //    'last-optout-date', 
                'tags', 
                'city', 
                'gender'),
            $results);

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

        ##reinitialize model
        $this->Participant->create();
        $this->Participant->id = $savedParticipant['Participant']['_id'];
        $resetParticipant = $this->Participant->reset();

        $this->assertEqual(1, $this->Participant->find('count'));
        $this->assertNotEqual($resetParticipant['Participant']['session-id'], null);
        $this->assertEqual($resetParticipant['Participant']['tags'], array());
        $this->assertEqual($resetParticipant['Participant']['profile'], array());

    }
    
    
    //TEST IMPORT
    public function testImport_otherFormat_fail()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import('testUrl', TESTS.'files/wellformattedparticipants.xlsx', null);

        $this->assertFalse($report);
        $this->assertEquals(
            'The file format xlsx is not supported.',
            $this->Participant->importErrors[0]);
    }
    
    
    public function testImport_duplicate_csv()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import('testUrl', TESTS.'files/duplicate_participants.csv', null);

        $this->assertEquals(2, count($report));
        $participants = $this->Participant->find('all');
        $this->assertEquals(2, count($participants));
    }
    
    
    public function testImport_duplicate_xls()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import('testUrl', TESTS.'files/duplicate_participants.xls', null);

        $this->assertEquals(2, count($report));
        $participants = $this->Participant->find('all');
        $this->assertEquals(2, count($participants));
    }
    
    
    public function testImport_Csv()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import('testUrl', TESTS.'files/well_formatted_participants.csv', null);

        $participants = $this->Participant->find('all');
        $this->assertEquals(2, count($participants));
        $this->assertEquals($participants[0]['Participant']['tags'], array('imported'));
        $this->assertEquals(
            $participants[0]['Participant']['profile'][0], 
            array('label' => 'Name',
                'value' => 'Olivier Vernin',
                'raw' => null));
        $this->assertEquals(
            $participants[0]['Participant']['profile'][1], 
            array('label' => 'DoB',
                'value' => '21st of July',
                'raw' => null));
        $this->assertEquals(
            $participants[1]['Participant']['profile'][0], 
            array('label' => 'Name',
                'value' => 'Gerald Ankunda',
                'raw' => null));
        $this->assertEquals(
            $participants[1]['Participant']['profile'][1], 
            array('label' => 'DoB',
                'value' => '30th of March',
                'raw' => null));  
    }
    
    
    public function testImport_csv_and_tagging() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/well_formatted_participants.csv',
            '1tag, other tag, stillAnother Tag');

        $participants = $this->Participant->find('all');
        $this->assertEquals(2, count($participants));
        $this->assertEquals(
            $participants[0]['Participant']['tags'], 
            array('imported', '1tag', 'other tag', 'stillAnother Tag'));
    }
    
    
    public function testImport_csv_with_tag() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/well_formatted_participants_with_tags.csv');

        $this->assertEquals(2, $this->Participant->find('count'));
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256788601462')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported', 'a first tag', 'a second tag'));

        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256712747841')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported', 'a 3rd tag'));
    }
    
    
    public function testImport_csv_replaceTagsAndLabels() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/well_formatted_participants_with_tags.csv');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/well_formatted_participants_with_tags_2.csv',
            null,
            null,
            true);

        $this->assertEquals(2, $this->Participant->find('count'));
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256788601462')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported', 'another tag'));
        $this->assertEquals(
            $participant['Participant']['profile'], 
            array(
                array('label' => 'Name', 
                    'value' => 'Olivier',
                    'raw' => null),
                array('label' => 'Town', 
                    'value' => 'Mombasa',
                    'raw' => null)));

        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256712747841')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported'));
    }
    
    
    public function testImport_csv_replaceTagsAndLabels_empty() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/well_formatted_participants_with_tags.csv');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/no_label_one_column_2.csv',
            null,
            null,
            true);

        $this->assertEquals(2, $this->Participant->find('count'));
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256788601462')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported'));
        $this->assertEquals(
            $participant['Participant']['profile'], 
            array()
            );
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256712747841')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported'));
    }
    
    
    public function testImport_csv_duplicate() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $this->Participant->create();
        $this->Participant->save(array(
            'phone' => '+256712747841',
            'name' => 'Gerald'));

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/well_formatted_participants.csv');

        $this->assertEquals(2, $this->Participant->find('count'));
        $this->assertEquals(
            array(
                'phone' => '+256788601462',
                'saved' => true,
                'exist-before' => false,
                'message' => array('Insert ok'),
                'line' => 2),
            $report[0]);
        $this->assertEquals(
            array(
                'phone' => '+256712747841',
                'saved' => false,
                'exist-before' => true,
                'message' => array('This phone number already exists in the participant list.'),
                'line' => 3),
            $report[1]);
    }
    
    
    public function testImport_csv_emptyColumn() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/empty_column.csv');

        $participants = $this->Participant->find('all');
        $this->assertEquals(2, count($participants));
        $this->assertEquals(isset($participants[0]['Participant']['profile'][0]), false);
        $this->assertEquals(isset($participants[1]['Participant']['profile'][0]), true);
    }
    
    
    public function testImport_csv_noLabelOneColumn_ok() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/no_label_one_column.csv');

        $participants = $this->Participant->find('all');
        $this->assertEquals(5, count($participants));
    }
    
    
    public function testImport_csv_noLabelTwoColumns_fail() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/no_label_two_columns.csv');

        $participants = $this->Participant->find('all');
        $this->assertEquals(0, count($participants));
    }
    
    
    public function testImport_csv_labelwrongline() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/label_wrong_line.csv');

        $participants = $this->Participant->find('all');
        $this->assertEquals(5, count($participants));
    }
    
    
    public function testImport_xls_duplicate() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $this->Participant->create();
        $this->Participant->save(
            array(
                'phone' => '256712747841',
                'name' => 'Gerald'));

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/well_formatted_participants.xls');

        $this->assertEquals(2, $this->Participant->find('count'));
        $this->assertEquals(
            array(
                'phone' => '+256788601462',
                'saved' => true,
                'exist-before' => false,
                'message' => array('Insert ok'),
                'line' => 2),
            $report[0]
            );
        $this->assertEquals(
            array(
                'phone' => '256712747841',
                'saved' => false,
                'exist-before' => true,
                'message' => array('This phone number already exists in the participant list.'),
                'line' => 3),
            $report[1]
            );
    }
    
    
    public function testImport_xls_wellFromated() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/well_formatted_participants.xls');

        $participants = $this->Participant->find('all');
        $this->assertEquals(2, count($participants));
        $this->assertEquals($participants[0]['Participant']['profile'][0]['label'], 'Name');
        $this->assertEquals($participants[0]['Participant']['profile'][0]['value'], 'Olivier Vernin');
        $this->assertEquals($participants[0]['Participant']['profile'][0]['raw'], '');  
        $this->assertEquals($participants[0]['Participant']['profile'][1]['label'], 'Age');
        $this->assertEquals($participants[0]['Participant']['profile'][1]['value'], '33');
        $this->assertEquals($participants[0]['Participant']['profile'][1]['raw'], '');
        $this->assertEquals($participants[1]['Participant']['profile'][0]['value'], 'Gerald Ankunda');
        $this->assertEquals($participants[1]['Participant']['profile'][1]['value'], '26');
    }
    
    
    public function testImport_xls_tag() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/well_formatted_participants.xls',           
            '1tag, other tag, stillAnother Tag');

        $participants = $this->Participant->find('all');
        $this->assertEquals(2, count($participants));
        $this->assertEquals($participants[0]['Participant']['tags'], array('imported', '1tag', 'other tag', "stillAnother Tag"));

    }
    
    
    public function testImport_xls_with_tags() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/well_formatted_participants_with_tags.xls');

        $this->assertEquals(2, $this->Participant->find('count'));
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256788601462')));
        $this->assertEquals(
            $participant['Participant']['tags'],           
            array('imported', 'one tag', 'a second tag'));
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256712747841')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported', 'a 3rd tag'));        
    }
    
    
    public function testImport_xls_replaceTagsAndLabels() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/well_formatted_participants_with_tags.xls');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/well_formatted_participants_with_tags_2.xls',
            null,
            null,
            true);
        $this->assertEquals(2, $this->Participant->find('count'));
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256788601462')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported', 'another tag'));
        $this->assertEquals(
            $participant['Participant']['profile'], 
            array(
                array('label' => 'Name', 
                    'value' => 'Olivier',
                    'raw' => null),
                array('label' => 'Town', 
                    'value' => 'Mombasa',
                    'raw' => null)));

        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256712747841')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported'));
    }
    
    
    public function testImport_xls_replaceTagsAndLabels_empty() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/well_formatted_participants_with_tags.xls');

        $report = $this->Participant->import(
            'testUrl',
            TESTS.'files/no_label_one_column_2.xls',
            null,
            null,
            true);
        $this->assertEquals(2, $this->Participant->find('count'));
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256788601462')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported'));
        $this->assertEquals(
            $participant['Participant']['profile'], 
            array());

        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256712747841')));
        $this->assertEquals(
            $participant['Participant']['tags'], 
            array('imported'));
    }
    
    
    public function testImport_xls_emptyColumn() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/empty_column.xls');

        $this->assertEquals(2, $this->Participant->find('count'));
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256777777777')));
        $this->assertEquals($participant['Participant']['profile'],array());
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+256888888888')));
        $this->assertEquals(
            $participant['Participant']['profile'], 
            array(array(
                'label' => 'name',
                'value' => 'oliv',
                'raw' => null)));
    }
    
    
    public function testImport_xls_noLabelOneColumn_ok() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/no_label_one_column.xls');

        $participants = $this->Participant->find('all');
        $this->assertEquals(5, count($participants));
    }
    
    
    public function testImport_xls_noLabelTwoColumns_fail() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/no_label_two_columns.xls');

        $participants = $this->Participant->find('all');
        $this->assertEquals(0, count($participants));
    }
    
    
    public function testImport_xls_labelwrongline() 
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/label_wrong_line.xls');

        $participants = $this->Participant->find('all');
        $this->assertEquals(5, count($participants));
    }
    
    
    public function testImport_csv_manyEmptyRows()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/many_empty_rows.csv');

        $participants = $this->Participant->find('all');

        $this->assertEquals(6, count($participants));
        $this->assertEquals(6, count($report));
    }
    
    
    public function testImport_xls_manyEmptyRows()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import(
            'testUrl',
            TESTS . 'files/many_empty_rows.xls');

        $participants = $this->Participant->find('all');

        $this->assertEquals(6, count($participants));
        $this->assertEquals(6, count($report));
    }
    
    
    public function testImport_taging_fail()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $report = $this->Participant->import('testUrl', TESTS.'files/wellformattedparticipants.xlsx', 'max$hi');


        $this->assertFalse($report);
        $this->assertEquals(
            'Error a tag is not valid: max$hi.',
            $this->Participant->importErrors[0]);
    }
    
    
    //TEST FILTERS
    public function testFromFilterToCondition_phone() 
    {
        $filter = array(
            1 => 'phone', 
            2 => 'equal-to', 
            3 => '+255');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array("phone" => "+255"));

        $filter = array(
            1 => "phone", 
            2 => "start-with", 
            3 => "+255");
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array("phone" => array('$regex' => "^\\+255")));

        $filter = array(
            1 => 'phone', 
            2 => 'start-with-any', 
            3 => '+255, +256');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('$or' => 
                array(
                    array("phone" => array('$regex' => "^\\+255")),
                    array("phone" => array('$regex' => "^\\+256"))))
            );
    }
    
    public function testFromFilterToCondition_phone_simulated() 
    {
        $filter = array(
            1 => 'phone', 
            2 => 'simulated');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('simulate' => true));
    }
    
    
    public function testFromFilterToCondition_enrolled()
    {
        $filter = array(
            1 => 'enrolled', 
            2 => 'in', 
            3 => '1');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('enrolled.dialogue-id' => '1'));

        $filter = array(
            1 => 'enrolled', 
            2 => 'not-in', 
            3 => '1');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('enrolled.dialogue-id' => array('$ne' => '1')));

    }
    
    
    public function testFromFilterToCondition_optin()
    {
        $filter = array(
            1 => 'optin', 
            2 => 'now');    
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('session-id' => array('$ne' => null)));

        $filter = array(
            1 => 'optin', 
            2 => 'date-from',
            3 => '21/01/2013');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('last-optin-date' => array('$gt' => '2013-01-21T00:00:00')));

        $filter = array(
            1 => 'optin', 
            2 => 'date-to',
            3 => '21/01/2013');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('last-optin-date' => array('$lt' => '2013-01-21T00:00:00')));
    }
    
    
    public function testFromFilterToCondition_optout()
    {
        $filter = array(
            1 => 'optout', 
            2 => 'now');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('session-id' => null));

        $filter = array(
            1 => 'optout', 
            2 => 'date-from',
            3 => '21/01/2013');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('last-optout-date' => array('$gt' =>  '2013-01-21T00:00:00')));

        $filter = array(
            1 => 'optout', 
            2 => 'date-to',
            3 => '21/01/2013');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('last-optout-date' => array('$lt' =>  '2013-01-21T00:00:00')));
    }
    
    
    public function testFromFilterToCondition_tag()
    {
        $filter = array(
            1 => 'tagged', 
            2 => 'with',
            3 => 'geek');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('tags' => 'geek'));

        $filter = array(
            1 => 'tagged', 
            2 => 'not-with',
            3 => 'geek');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array('tags' => array('$ne' => 'geek')));
    }
    
    
    public function testFromFilterToCondition_label()
    {
        $filter = array(
            1 => 'labelled', 
            2 => 'with',
            3 => 'gender:male');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array(
                'profile' => array(
                    '$elemMatch' => array(
                        'label' => 'gender',
                        'value' => 'male'))));

        $filter = array(
            1 => 'labelled', 
            2 => 'not-with',
            3 => 'gender:male');
        $this->assertEqual(
            $this->Participant->fromFilterToQueryCondition($filter),
            array(
                'profile' => array(
                    '$elemMatch' => array(
                        '$or' => array(
                            array('label' => array('$ne' => 'gender')),
                            array('value' => array('$ne' => 'male')))))));
    }
    
    
    public function testAddMassTags_filter()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $this->assertEqual(array(), $this->Participant->getDistinctTagsAndLabels());

        $participant_08 = array(
            'phone' => '+8',
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
            'phone' => '+9',
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

        //Mass tag all participant with phone +8
        $conditions = array(
            'phone' => '+8');       

        $this->Participant->addMassTags('hi', $conditions);

        $participant = $this->Participant->find('first', array('conditions' => $conditions));         
        $this->assertEqual(array('geek', 'cool', 'hi'), $participant['Participant']['tags']);

        //Mass tag all participant with tag geek
        $conditions = array(
            'tags' => 'hi');
        $this->Participant->addMassTags('nerd', $conditions);
        $participant = $this->Participant->find('first', array('conditions' => $conditions));
        $this->assertEqual(array('geek', 'cool', 'hi', 'nerd'), $participant['Participant']['tags']);

        //Double mass tag
        $this->Participant->addMassTags('nerd', $conditions);
        $participant = $this->Participant->find('first', array('conditions' => $conditions));
        $this->assertEqual(array('geek', 'cool', 'hi', 'nerd'), $participant['Participant']['tags']);

        //Mass tag all particiant that don't have tag
        $conditions = array(
            'tags' => array('$ne' => 'hi'));
        $this->Participant->addMassTags('bye', $conditions);
        $participant = $this->Participant->find('first', array('conditions' => $conditions));
        $this->assertEqual(array('geek', 'another tag', 'bye'), $participant['Participant']['tags']);

        //Mass tag all participant that tagged geek and not hi
        $conditions = array(
            '$and' => array(
                array('tags' => array('$ne' => 'hi')),
                array('tags' => 'geek')));
        $this->Participant->addMassTags('last tag', $conditions);
        $participant = $this->Participant->find('first', array('conditions' => $conditions));
        $this->assertEqual(array('geek', 'another tag', 'bye', 'last tag'), $participant['Participant']['tags']); 

    }
    
    
    public function testAddMassTags_trim()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $this->assertEqual(array(), $this->Participant->getDistinctTagsAndLabels());

        $participant_08 = array(
            'phone' => '+8',
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
            'phone' => '+9',
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

        $conditions = array(
            'phone' => '+8');   

        $this->Participant->addMassTags(' hi ', $conditions);
        $participant = $this->Participant->find('first', array('conditions' => $conditions));         
        $this->assertEqual(array('geek', 'cool', 'hi'), $participant['Participant']['tags']);       

    }
    
    
    public function testAddMassTags_failValidation()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant_08 = array(
            'phone' => '+8',
            );
        $this->Participant->create();
        $this->Participant->save($participant_08);

        $conditions = array();    
        $this->assertEqual(
            "Use only space, letters and numbers for tag, e.g 'group 1'.",
            $this->Participant->addMassTags('%', $conditions));       

        $this->assertTrue($this->Participant->addMassTags('you2', $conditions)); 
        $participant = $this->Participant->find('first', $conditions);
        $this->assertTrue(in_array('you2', $participant['Participant']['tags']));
    }
    
    
    public function testParticipantProfile_trim()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $this->assertEqual(array(), $this->Participant->getDistinctTagsAndLabels());    
        $participant_08 = array(
            'phone' => '+8',            
            );
        $this->Participant->create();
        $savedParticipant                           = $this->Participant->save($participant_08);     
        $savedParticipant['Participant']['profile'] = ' city: kampala, name: mama';
        $new                                        = $this->Participant->save($savedParticipant);
        $participantDb                              = $this->Participant->find();

        $this->assertEqual($participantDb['Participant']['profile'][0]['label'],'city');
        $this->assertEqual($participantDb['Participant']['profile'][1]['label'],'name');
        $this->assertEqual($participantDb['Participant']['profile'][0]['value'],'kampala');
        $this->assertEqual($participantDb['Participant']['profile'][1]['value'],'mama');

    }
    
    
    public function testUntag_trim()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant_08 = array(
            'phone' => '+8',
            'tags' => array('geek', 'cool', 'hi'),
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
            'phone' => '+9',
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
        $this->Participant->deleteMassTags(' geek', array());
        $allTags = $this->Participant->getDistinctTags();
        $this->assertEqual(array('cool', 'hi', 'another tag'), $allTags);      

    }
    
    
    public function testDeleteTags_Form_FilterParams()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');


        $participant_08 = array(
            'phone' => '+8',
            'tags' => array('geek', 'cool', 'hi'),
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
            'phone' => '+9',
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


        $conditions = array(
            'phone' => '+8'); 
        $this->Participant->deleteMassTags('hi', $conditions);
        $participant = $this->Participant->find('first', array('conditions' => $conditions));         
        $this->assertEqual(array('geek', 'cool'), $participant['Participant']['tags']); 

    }
    
    
    public function testcleanPhone()
    {
        $this->assertEqual("+254700866920", Participant::cleanPhone(" +254700866920 "));
        $this->assertEqual("+254700866920", Participant::cleanPhone("254700866920"));
        $this->assertEqual("+254700866920", Participant::cleanPhone("254 700 866 920"));
        $this->assertEqual("+254700866920", Participant::cleanPhone("00254700866920"));
        $this->assertEqual("+254700866920", Participant::cleanPhone("+254700866920�"));
        $this->assertEqual("+254700866920", Participant::cleanPhone(" +2547OO866920 "));
        $this->assertEqual("#254700866920", Participant::cleanPhone(" #2547OO866920 "));
    }
    
    
    public function testAddMassTags_noduplicate_tags()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $this->assertEqual(array(), $this->Participant->getDistinctTagsAndLabels());

        $participant_08 = array(
            'phone' => '+8',
            'tags' => array('geek', 'cool', 'hi'),
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
            'phone' => '+9',
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

        $conditions = array();   

        $this->Participant->addMassTags(' hi ', $conditions);
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+8')));
        $this->assertEqual(array('geek', 'cool', 'hi'), $participant['Participant']['tags']);
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+9')));
        $this->assertEqual(array('geek', 'another tag', 'hi'), $participant['Participant']['tags']);
    }
    
    
    public function testAddMassTags_noduplicate_tags_with_filter()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $this->assertEqual(array(), $this->Participant->getDistinctTagsAndLabels());

        $participant_08 = array(
            'phone' => '+8',
            'tags' => array('geek', 'cool', 'hi'));
        $this->Participant->create();
        $this->Participant->save($participant_08);

        $participant_09 = array(
            'phone' => '+9',
            'tags' => array('geek', 'another tag'));                                                                   

        $this->Participant->create();
        $this->Participant->save($participant_09);   

        $conditions = array(
            'tags' => 'cool');      

        $this->Participant->addMassTags('hi', $conditions);

        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+8')));
        $this->assertEqual(array('geek', 'cool', 'hi'), $participant['Participant']['tags']);
        $participant = $this->Participant->find('first', array('conditions' => array('phone' => '+9')));
        $this->assertEqual(array('geek', 'another tag'), $participant['Participant']['tags']);
    }
    
    
    public function testFindAllSafeJoin() 
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $this->Participant->MAX_JOIN = 2; //force to split requests for every 2 phones in the cursor

        $iter = new ArrayIterator(array(
            array("_id" => "+254100000000"),
            array("_id" => "+254100000001"),
            array("_id" => "+254100000002"),
            array("_id" => "+254100000003"),
            array("_id" => "+254100000004"),
            array("_id" => "+254100000005")));

        $query = array(
            'phone' => array(
                '$join' => $iter));

        $participantStart = array('phone' => '+254100000000');
        $this->Participant->create();
        $this->Participant->save($participantStart);

        $participantLast = array('phone' => '+254100000003');
        $this->Participant->create();
        $this->Participant->save($participantLast);

        $results = $this->Participant->find('allSafeJoin', array(
            'limit' => 10,
            'conditions' => $query));
        $this->assertEqual(2, count($results));
    }
    
    
    public function testValideRunActions()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant = array('phone' => '+06');
        $this->Participant->create();
        $result = $this->Participant->save($participant);

        $dialogue = $this->Maker->getOneDialogueWithKeyword();
        $this->Dialogue->create();
        $savedDialogue = $this->Dialogue->save($dialogue);
        $this->Dialogue->makeActive();

        $runActions = array(
            'phone'=> '+06',
            'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
            'interaction-id' => $savedDialogue['Dialogue']['interactions'][1]['interaction-id'],
            'answer' => 'bad');

        $result = $this->Participant->validateRunActions($runActions);
        $this->assertTrue($result);
    }
    
    
    public function testValideRunActions_fail_noParticipant()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $dialogue = $this->Maker->getOneDialogueWithKeyword();
        $this->Dialogue->create();
        $savedDialogue = $this->Dialogue->save($dialogue);
        $this->Dialogue->makeActive();

        $runActions = array(
            'phone'=> '+06',
            'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
            'interaction-id' => $savedDialogue['Dialogue']['interactions'][1]['interaction-id'],
            'answer' => 'bad');

        $result = $this->Participant->validateRunActions($runActions);
        $this->assertEqual(array('phone' => "No participant with phone: +06."), $result);
    }
    
    
    public function testValideRunActions_fail_noDialogue()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant = array('phone' => '+06');
        $this->Participant->create();
        $result = $this->Participant->save($participant);

        $runActions = array(
            'phone'=> '+06',
            'dialogue-id' => 'someId',
            'interaction-id' => 'someOtherId',
            'answer' => 'bad');

        $result = $this->Participant->validateRunActions($runActions);
        $this->assertEqual(
            array('dialogue-id' => "No dialogue with id: someId."),
            $result);
    }
    
    
    public function testValideRunActions_fail_noInteraction()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');

        $participant = array('phone' => '+06');
        $this->Participant->create();
        $result = $this->Participant->save($participant);

        $dialogue = $this->Maker->getOneDialogueWithKeyword();
        $this->Dialogue->create();
        $savedDialogue = $this->Dialogue->save($dialogue);
        $this->Dialogue->makeActive();

        $runActions = array(
            'phone'=> '+06',
            'dialogue-id' => $savedDialogue['Dialogue']['dialogue-id'],
            'interaction-id' => 'someOtherId',
            'answer' => 'bad');

        $result = $this->Participant->validateRunActions($runActions);
        $this->assertEqual(
            array('interaction-id' => "The dialogue with id ".$savedDialogue['Dialogue']['dialogue-id']." doesn't have an interaction with id someOtherId"),
            $result);
    }

    
}
