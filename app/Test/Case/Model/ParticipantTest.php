<?php 
App::uses('Participant', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');

class ParticipantTestCase extends CakeTestCase
{
    
    public function setUp()
    {
        parent::setUp();

        $option            = array('database'=>'test');
        $this->Participant = new Participant($option);

        $this->Participant->setDataSource('mongo_test');

        $this->dropData();
    }


    public function tearDown()
    {
        unset($this->Participant);
        parent::tearDown();
    }


    public function dropData()
    {
        $this->Participant->deleteAll(true, false);
    }


    public function testSave_createData()
    {
        $participant = array(
            'phone' => '+788601461',
            'last-optin-date' => '2012-12-02T12:32:00',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertRegExp('/^[0-9a-fA-F]{32}/', $savedParticipant['Participant']['session-id']);
        $this->assertTrue(is_array( $savedParticipant['Participant']['tags']));
        $this->assertTrue(is_array( $savedParticipant['Participant']['enrolled']));
        $this->assertTrue(is_array( $savedParticipant['Participant']['profile']));
    }


    public function testSave_clearPhone()
    {
        //1st assertion phone is already in the correct format
        $participant = array(
            'phone' => '+788601462',
            'last-optin-date' => '2012-12-02T12:32:00',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual('+788601462', $savedParticipant['Participant']['phone']);
        //$this->assertTrue(isset($savedParticipant['Participant']['session-id']));

        //2nd assertion phone is a number
        $participant = array(
            'phone' => 788601463,
            'last-optin-date' => '2012-12-02T12:32:00',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual("+788601463", $savedParticipant['Participant']['phone']);

        //Phone with letter O instead of 0 digit is NOT SAVED
        $participant = array(
            'phone' => 'OO7886O1464',
            'last-optin-date' => '2012-12-02T12:32:00',
            );
        $this->Participant->create();
        $this->assertFalse($this->Participant->save($participant));

        //The double 00 are replace by a +
        $participant = array(
            'phone' => '00788601465',
            'last-optin-date' => '2012-12-02T12:32:00',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual("+788601465", $savedParticipant['Participant']['phone']);

        //The single 0 is replace by a +
        $participant = array(
            'phone' => '0788601466',
            'last-optin-date' => '2012-12-02T12:32:00',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual("+788601466", $savedParticipant['Participant']['phone']);
        
         //The phone is trimmed 
        $participant = array(
            'phone' => ' 0788601467 ',
            'last-optin-date' => '2012-12-02T12:32:00',
            );
        $this->Participant->create();
        $savedParticipant = $this->Participant->save($participant);
        $this->assertEqual("+788601467", $savedParticipant['Participant']['phone']);
    }


}
