<?php 
App::uses('Participant', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('Dialogue', 'Model');
App::uses('ScriptMaker', 'Lib');
App::uses('MongodbSource', 'Mongodb.MongodbSource');
App::uses('FilterException', 'Lib');

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


    public function testSave()
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



    public function testSave_fail()
    {
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
     
        $participant = array(
            'phone' => '+788601461',
            );
        $this->Participant->create();
        $this->Participant->save($participant);

        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);

        $this->assertFalse($savedParticipant);
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
        
        $resetParticipant =$this->Participant->reset($savedParticipant['Participant']);
        
        $this->assertNotEqual($resetParticipant['session-id'], null);
        $this->assertEqual($resetParticipant['tags'], array());
        $this->assertEqual($resetParticipant['profile'], array());
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


    //TEST FILTERS
    public function testFromFilterToQueryConditions_phone() 
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'phone', 
                    2 => 'equal-to', 
                    3 => '+255')
                )
            );
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array("phone" => "+255"));

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => "phone", 
                    2 => "start-with", 
                    3 => "+255")
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array("phone" => new MongoRegex("/^\\+255/")));

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'phone', 
                    2 => 'start-with-any', 
                    3 => '+255, +256')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('$or' => 
                array(
                    array("phone" => new MongoRegex("/^\\+255/")),
                    array("phone" => new MongoRegex("/^\\+256/"))
                    )
                )
            );
    }

    public function testFromFilterToQueryConditions_enrolled()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'enrolled', 
                    2 => 'in', 
                    3 => '1')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('enrolled.dialogue-id' => '1')
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'enrolled', 
                    2 => 'not-in', 
                    3 => '1')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('enrolled.dialogue-id' => array('$ne' => '1'))
            );

    }

    public function testFromFilterToQueryConditions_optin()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'optin', 
                    2 => 'now')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('session-id' => array('$ne' => null))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'optin', 
                    2 => 'date-from',
                    3 => '21/01/2013')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('last-optin-date' => array('$gt' => '2013-01-21T00:00:00'))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'optin', 
                    2 => 'date-to',
                    3 => '21/01/2013')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('last-optin-date' => array('$lt' => '2013-01-21T00:00:00'))
            );
    }

    public function testFromFilterToQueryConditions_optout()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'optout', 
                    2 => 'now')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('session-id' => null)
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'optout', 
                    2 => 'date-from',
                    3 => '21/01/2013')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('last-optout-date' => array('$gt' =>  '2013-01-21T00:00:00'))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'optout', 
                    2 => 'date-to',
                    3 => '21/01/2013')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('last-optout-date' => array('$lt' =>  '2013-01-21T00:00:00'))
            );

    }


    public function testFromFilterToQueryConditions_tag()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'tagged', 
                    2 => 'in',
                    3 => 'geek')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('tags' => 'geek')
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'tagged', 
                    2 => 'not-in',
                    3 => 'geek')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('tags' => array('$ne' => 'geek'))
            );
    }

    public function testFromFilterToQueryConditions_label()
    {
        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'labelled', 
                    2 => 'in',
                    3 => 'gender:male')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('profile' => array(
                '$elemMatch' => array(
                    'label' => 'gender',
                    'value' => 'male')
                ))
            );

        $filter = array(
            'filter_operator' => 'all',
            'filter_param' => array(
                array(
                    1 => 'labelled', 
                    2 => 'not-in',
                    3 => 'gender:male')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('profile' => array(
                '$elemMatch' => array(
                    '$or' => array(
                        array('label' => array('$ne' => 'gender')),
                        array('value' => array('$ne' => 'male')))
                )))
            );
    }


    public function testFromFilterToQueryConditions_validationFail()
    {

        $filterParam = array();
        try {        
            $this->Participant->validateFilter($filterParam);
            $this->failed('Missing field should rise an exception');
        } catch (FilterException $e) {
            $this->assertEqual($e->getMessage(), "Field is missing.");
        }

        $filterParam = array(
            1 => "somefield");
        try {
            $this->Participant->validateFilter($filterParam);
            $this->failed('Not supported field should rise an exception');
        } catch (FilterException $e) {
            $this->assertEqual($e->getMessage(), "Field 'somefield' is not supported.");
        }

        $filterParam = array(
            1 => "labelled");
        try {
            $this->Participant->validateFilter($filterParam);
            $this->failed('Missing Operator should rise an exception');
        } catch (FilterException $e) {
            $this->assertEqual($e->getMessage(), "Operator is missing for field 'labelled'.");
        }

        $filterParam = array(
            1 => "labelled", 
            2 => "someoperator");
        try {
            $this->Participant->validateFilter($filterParam);
            $this->failed('Not supported operator should rise an exception');
        } catch (FilterException $e) {
            $this->assertEqual($e->getMessage(), "Operator 'someoperator' not supported for field 'labelled'.");
        }

        $filterParam = array(
            1 => "labelled", 
            2 => "in");
        try {
            $this->Participant->validateFilter($filterParam);
            $this->failed('Not supported operator should rise an exception');
        } catch (FilterException $e) {
            $this->assertEqual($e->getMessage(), "Parameter is missing for field 'labelled'.");
        }
        
    }


    public function testFromFilterToQueryConditions_OR() 
    {
        $filter = array(
            'filter_operator' => 'any',
            'filter_param' => array(
                array(
                    1 => 'phone', 
                    2 => 'equal-to', 
                    3 => '+255')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('phone' => '+255'));

        $filter = array(
            'filter_operator' => 'any',
            'filter_param' => array(
                array(
                    1 => 'phone', 
                    2 => 'equal-to', 
                    3 => '+255'),
                array(
                    1 => 'optin', 
                    2 => 'now')
                )
            );        
        $this->assertEqual(
            $this->Participant->fromFilterToQueryConditions($filter),
            array('$or' => 
                array(
                    array('phone' => '+255'),
                    array('session-id' => array('$ne'=>null))
                    )
                )
            );
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
        
        $conditions = array(
            'phone' => '+8');       
        
        $this->Participant->addMassTags('hi', $conditions);

        $participant = $this->Participant->find('first', array('conditions' => $conditions));                 
        $this->assertEqual(array('geek', 'cool', 'hi'), $participant['Participant']['tags']);       
        
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
        $results = $this->Participant->addMassTags('%', $conditions);       
        $this->assertFalse($results);
        
        $this->Participant->addMassTags('you2', $conditions); 
        $participants = $this->Participant->find('all', $conditions);  
        $this->assertTrue(in_array('you2', $participants[0]['Participant']['tags']));
    }

	public function testParticipantProfile_trim()
	{
			$this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
			
			$this->assertEqual(array(), $this->Participant->getDistinctTagsAndLabels());			
			$participant_08 = array(
					'phone' => '+8',    				
					);
			$this->Participant->create();
			$savedParticipant = $this->Participant->save($participant_08);     		
			$savedParticipant['Participant']['profile'] = ' city: kampala, name: mama';
			$new = $this->Participant->save($savedParticipant);
			$participantDb = $this->Participant->find();			
			$this->assertEqual($participantDb['Participant']['profile'][0]['label'],'city');
			$this->assertEqual($participantDb['Participant']['profile'][1]['label'],'name');
			$this->assertEqual($participantDb['Participant']['profile'][0]['value'],'kampala');
			$this->assertEqual($participantDb['Participant']['profile'][1]['value'],'mama');
			
	}


}
