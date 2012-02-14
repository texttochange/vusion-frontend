<?php
/* Program Test cases generated on: 2012-01-24 15:57:36 : 1327409856*/
App::uses('Program', 'Model');

/**
 * Program Test Case
 *
 */
class ProgramTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.program', 'app.user', 'app.programsUser');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Program = ClassRegistry::init('Program');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Program);

		parent::tearDown();
	}

	public function testFind() {
		$result = $this->Program->find();
		$expected = array(
				'Program' => array(
					'id' => 2,
					'name' => 'm6h',
					'country' => 'congo',
					'url' => 'm6h',
					'database' => 'm6h',			
					'created' => '2012-01-24 15:29:24',
					'modified' => '2012-01-24 15:29:24'
				),
				'Program' => array(
					'id' => 1,
					'name' => 'test',
					'country' => 'uganda',
					'url' => 'test',
					'database' => 'test',
					'created' => '2012-01-24 15:29:24',
					'modified' => '2012-01-24 15:29:24'
				),
				'User' => array(
					0 => array(
						'id' => 1,
						'username' => 'Lorem ipsum dolor sit amet',
						'password' => 'Lorem ipsum dolor sit amet',
						'group_id' => 1,
						'limited_program_access' => true,
						'created' => '2012-01-24 15:34:07',
						'modified' => '2012-01-24 15:34:07',
						'ProgramsUser' => array(
								'id' => 1,
								'program_id' => '1',
								'user_id' => '1',
							),
					),
				)
		);
		//Debugger::dump($result);
		$this->assertEquals($expected, $result);
		//$this->assertEquals(2, 1);
	}
	
	
}
