<?php
App::uses('UserLog', 'Model');

class UserLogTestCase extends CakeTestCase
{
    public function setup()
    {
        parent::setup();
        $this->UserLog = new UserLog();
        $this->dropData();
    }
    
    
    public function tearDown()
    {
       $this->dropData(); 
       unset($this->UserLog);
       parent::tearDown();
    }
    
    
    public function dropData()
    {   
        $this->UserLog->deleteAll(true, false);
    }
    
    
    public function testSaveUserLogs()
    {
        $userLog = array(
            'timestamp' => '2014-20-10T20:25:00',
            'timezone' => 'Africa/Kampala',
            'user-name' => 'maxmass',
            'user-id' => '1',
            'program-name' => 'm4rh program',
            'program-database-name' => 'm4rhP',
            'controller' => 'programParticipant',
            'action' => 'delete',
            'parameters' => 'all participant with tag: geek'
            );
        
        $this->UserLog->create();
        $savedUserLog = $this->UserLog->save($userLog);
        
        $userLog_02 = array(
            'timestamp' => '2014-20-10T20:25:00',
            'timezone' => 'Australia/Sydney',
            'user-name' => 'Tom',
            'user-id' => '1',
            'program-name' => 'm6rh program',
            'program-database-name' => 'm6rhP',
            'controller' => 'programParticipant',
            'action' => 'delete',
            'parameters' => 'all participant with tag: today'
            );
        
        $this->UserLog->create();
        $savedUserLog = $this->UserLog->save($userLog_02);
        
        $this->assertEqual(2, $this->UserLog->find('count'));
    }
    
    
    public function testSave_fail_emptyField()
    {
       $userLog = array(
            'timestamp' => '2014-20-10T20:25:00',
            'timezone' => 'Africa/Kampala',
            'user-name' => 'maxmass',
            'user-id' => '',
            'program-name' => 'm4rh program',
            'program-database-name' => 'm4rhP',
            'controller' => 'programParticipant',
            'action' => 'delete',
            'parameters' => 'all participant with tag: geek'
            );
        
        $this->UserLog->create();
        $savedUserLog = $this->UserLog->save($userLog);
        
        $this->assertFalse($savedUserLog);        
        $this->assertEquals(
            'The user id cannot be empty.',
            $this->UserLog->validationErrors['user-id'][0]
            );
    }
    
    
    public function testSave_fail_dateFormat()
    {
       $userLog = array(
            'timestamp' => '2014-20-10',
            'timezone' => 'Africa/Kampala',
            'user-name' => 'maxmass',
            'user-id' => '45',
            'program-name' => 'm4rh program',
            'program-database-name' => 'm4rhP',
            'controller' => 'programParticipant',
            'action' => 'delete',
            'parameters' => 'all participant with tag: geek'
            );
        
        $this->UserLog->create();
        $savedUserLog = $this->UserLog->save($userLog);
        
        $this->assertFalse($savedUserLog);
        $this->assertEquals(
            'The date time is not in an ISO format.',
            $this->UserLog->validationErrors['timestamp'][0]
            );
    }
    
    
}
