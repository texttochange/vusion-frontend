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
	public $fixtures = array('app.program');

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
					'name' => 'm5h',
					'country' => 'congo',
					'created' => '2012-01-24 15:29:24',
					'modified' => '2012-01-24 15:29:24'
				),
				'Program' => array(
					'id' => 1,
					'name' => 'm4h',
					'country' => 'uganda',
					'created' => '2012-01-24 15:29:24',
					'modified' => '2012-01-24 15:29:24'
				),
					
		);
		//Debugger::dump($result);
		$this->assertEquals($expected, $result);
		//$this->assertEquals(2, 1);
	}
	
	
}
