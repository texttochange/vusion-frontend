<?php
App::uses('UserLog', 'Model');

class UserLogTestCase extends CakeTestCase
{

	public function setUp()
	{
		parent::setUp();
		$this->UserLog = new UserLog();
	}

	public function tearDown() {
		unset($this->Userlog);
	}

	public function testValidate_fail_timestamp_format() 
	{
		$expected = array(
			'timestamp' => array('The timestamp format is not valid'));
		$userlog = array(
			'timestamp' => '2010-20-10T20:20',
			'user-name' => 'olivier@gmail.com',
			'user_id' => '1',
			'program' => 'my program',
			'controller' => 'programParticipant',
			'action' => 'delete',
			'parameters' => 'all participant with tag: geek');

		$this->UserLog->create();
		$r = $this->UserLog->save($userlog);
		$this->assertFalse(isset($r['UserLog']));
		print_r($this->UserLog->validationErrors);
		$this->assertEqual(
			$expected,
			$this->UserLog->validationErrors);
	}

	public function testValidate_ok() 
	{
		$userlog = array(
			'timestamp' => '2010-20-10T20:20:00',
			'user-name' => 'olivier@gmail.com',
			'user_id' => '1',
			'program' => 'my program',
			'controller' => 'programParticipant',
			'action' => 'delete',
			'parameters' => 'all participant with tag: geek');

		$this->UserLog->create();
		$r = $this->UserLog->save($userlog);
		$this->assertTrue(isset($r['UserLog']));
	}
}