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
        
        $userLog2 = array(
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
        $savedUserLog = $this->UserLog->save($userLog2);
        
        $this->assertEqual(2, $this->UserLog->find('count'));
    }
}
