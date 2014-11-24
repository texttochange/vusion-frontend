<?php
App::uses('ProgramSetting', 'Model');
App::uses('MongodbSource', 'Mongodb.MongodbSource');
App::uses('ProgramSpecificMongoModel', 'Model');


class ProgramSettingTestCase extends CakeTestCase
{
      
    public function setUp()
    {
        parent::setUp();
        $dbName = 'testdbprogram';
        $this->ProgramSetting = ProgramSpecificMongoModel::init(
            'ProgramSetting', $dbName);
        $this->dropData();
    }
    
    
    public function tearDown()
    {
        unset($this->ProgramSetting);
        
        parent::tearDown();
    }
    
    
    public function dropData()
    {        
        $this->ProgramSetting->deleteAll(true,false);
    }
    
    
    public function testGetProgramSetting_notInDatabase()
    {         
        $result = $this->ProgramSetting->find(
            'getProgramSetting', 
            array(
                'key'=>'shortcode', 
                'value' => '8282'
                )
            );
        
        $this->assertNull($result);    
    }
    
    
    public function testGetProgramSetting_searchNullInDatabase()
    {         
        $result = $this->ProgramSetting->find(
            'getProgramSetting',
            array(
                'key'=>'shortcode', 
                'value' => null
                )
            );
        
        $this->assertNull($result);    
    }
    
    
    public function testSaveProgramSetting()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', '8282');
        $this->assertEqual(1, count($this->ProgramSetting->find('count')));
        $this->assertEqual('8282', $this->ProgramSetting->find('getProgramSetting', array('key'=>'shortcode')));        
        $this->ProgramSetting->saveProgramSetting('shortcode', '8181');
        $this->assertEqual(1, count($this->ProgramSetting->find('count')));
        $this->assertEqual('8181', $this->ProgramSetting->find('getProgramSetting', array('key'=>'shortcode')));        
        
    }
    
    
    public function testGetProgramSettings()
    {
        $this->assertEqual(0, count($this->ProgramSetting->getProgramSettings()));
        $this->ProgramSetting->saveProgramSetting('shortcode', 'value1');
        $this->ProgramSetting->saveProgramSetting('timezone', 'value2');
        $this->assertEqual(
            array(
                'shortcode' => 'value1',
                'timezone'=>'value2'),
            $this->ProgramSetting->getProgramSettings()
            ); 
    }
    
    
    public function testGetProgramTimeNow()
    {
        $this->assertNull($this->ProgramSetting->getProgramTimeNow());        
        
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        $this->assertNotNull($this->ProgramSetting->getProgramTimeNow());
        
    }
    
    
    public function testIsNotPast()
    {
        
        $now = new DateTime('now');
        date_timezone_set($now, timezone_open('Africa/Kampala'));        
        $past = $now->modify('-1 hours');        
        
        $this->assertEqual(
            "The program settings are incomplete. Please specificy the Timezone.", 
            $this->ProgramSetting->isNotPast($past));        
        
        $this->ProgramSetting->saveProgramSetting('timezone', 'Africa/Kampala');
        
        $this->assertFalse($this->ProgramSetting->isNotPast($past));
        $future = $now->modify('+1 hours');
        $this->assertTrue($this->ProgramSetting->isNotPast($future));
    }
    
    
    public function testBeforeValidate()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', 'value1');
        $this->ProgramSetting->saveProgramSetting('timezone', 'value2');
        
        $settings = $this->ProgramSetting->find('all');
        
        $this->assertEqual($this->ProgramSetting->getModelVersion(), $settings[0]['ProgramSetting']['model-version']);
    }
    
    
    public function testBeforeValidate_requestAndFeedbackPrioritized()
    {
        $this->ProgramSetting->saveProgramSetting('shortcode', 'value1');
        $this->ProgramSetting->saveProgramSetting('timezone', 'value2');
        $this->ProgramSetting->saveProgramSetting('request-and-feedback-prioritized', '1');
        
        $settings = $this->ProgramSetting->find('all');
        
        $this->assertEqual('request-and-feedback-prioritized', $settings[2]['ProgramSetting']['key']);
        $this->assertEqual('prioritized', $settings[2]['ProgramSetting']['value']);
    }
    
    
    public function testSaveSettings_ok()
    {
        $settings = array(
            'shortcode' => '256-8181',
            'credit-type' => 'outgoing-only',
            'credit-number' => '2000',
            'credit-from-date' => '02/12/2013',
            'credit-to-date' => '03/12/2013',
            );
        
        $this->assertTrue($this->ProgramSetting->saveProgramSettings($settings));
        $this->assertEqual('outgoing-only', $this->ProgramSetting->find('getProgramSetting', array('key' => 'credit-type')));
        $this->assertEqual(2000, $this->ProgramSetting->find('getProgramSetting', array('key' => 'credit-number')));
        $this->assertEqual('2013-12-02T00:00:00', $this->ProgramSetting->find('getProgramSetting', array('key' => 'credit-from-date')));
        $this->assertEqual('2013-12-03T00:00:00', $this->ProgramSetting->find('getProgramSetting', array('key' => 'credit-to-date')));
    }
    

    public function testSaveSettings_fail_keywordAlreadyUsed() 
    {
        $settings = array(
            'shortcode' => '256-8181',
            );
         $usedKeywords = array(
            'eotherkeyword' => array(
                'program-db' => 'otherprogram', 
                'program-name' => 'Other Program', 
                'by-type' => 'Dialogue'));
         $this->assertFalse($this->ProgramSetting->saveProgramSettings($settings, $usedKeywords));
         $this->assertEqual(
             "'eotherkeyword' already used by a Dialogue of program 'Other Program'.",
             $this->ProgramSetting->validationErrors['shortcode'][0]);
    }

    
    public function testSaveSettings_ok_nolimit()
    {
        $settings = array(
            'shortcode' => '256-8181',
            'credit-type' => 'none',
            );
        
        $this->assertTrue($this->ProgramSetting->saveProgramSettings($settings));
        $this->assertEqual('none', $this->ProgramSetting->find('getProgramSetting', array('key' => 'credit-type')));
    }
    
    public function testSaveSettings_failMissingField()
    {
        $settings = array(
            'shortcode' => '256-8181',
            'credit-type' => 'outgoing-only',
            'credit-number' => '2000',
            'credit-from-date' => '02/12/2013',
            );
        
        $this->assertFalse($this->ProgramSetting->saveProgramSettings($settings));
        $this->assertEqual(0, $this->ProgramSetting->find('count'));
        $this->assertEqual(
            $this->ProgramSetting->validationErrors['credit-type'][0],
            'The credit-type field with value outgoing-only require the field credit-to-date.');
    }
    
    
    public function testSaveSettings_failNullField()
    {
        $settings = array(
            'shortcode' => '256-8181',
            'credit-type' => 'outgoing-only',
            'credit-number' => '2000',
            'credit-from-date' => '02/12/2013',
            'credit-to-date' => null,
            );
        
        $this->assertFalse($this->ProgramSetting->saveProgramSettings($settings));
        $this->assertEqual(0, $this->ProgramSetting->find('count'));
        $this->assertEqual(
            $this->ProgramSetting->validationErrors['credit-to-date'][0],
            'The format of the date has to be 15/02/2013.');
    }
    
    public function testSaveSettings_failDateNonValid()
    {
        $settings = array(
            'shortcode' => '256-8181',
            'credit-type' => 'outgoing-only',
            'credit-number' => '2000',
            'credit-from-date' => '03/12/2013',
            'credit-to-date' => '02/12/2013',
            );
        
        $this->assertFalse($this->ProgramSetting->saveProgramSettings($settings));
        $this->assertEqual(0, $this->ProgramSetting->find('count'));
        $this->assertEqual(
            $this->ProgramSetting->validationErrors['credit-from-date'][0],
            'This from date has to be before the to date.');
        $this->assertEqual(
            $this->ProgramSetting->validationErrors['credit-to-date'][0],
            'This to date has to be after the from date.');
    }
    
    
    public function testSaveSettings_smsForwardingAllow_ok() 
    {
        $settings = array(
            'shortcode' => '256-8181',
            'sms-forwarding-allowed' => 'none'
            );
        
        $this->assertTrue($this->ProgramSetting->saveProgramSettings($settings));
    }
    
    
    public function testSaveSettings_smsForwardingAllow_fail() 
    {
        $settings = array(
            'shortcode' => '256-8181',
            'sms-forwarding-allowed' => 'not-allowed-value'
            );
        
        $this->assertFalse($this->ProgramSetting->saveProgramSettings($settings));
        $this->assertEqual(
            'The sms forwarding value is not valid.',
            $this->ProgramSetting->validationErrors['sms-forwarding-allowed'][0]);
    }

}

