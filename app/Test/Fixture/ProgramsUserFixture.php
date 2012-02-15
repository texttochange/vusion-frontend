<?php
/* ProgramsUser Fixture generated on: 2012-02-14 13:38:35 : 1329226715 */

/**
 * ProgramsUserFixture
 *
 */
class ProgramsUserFixture extends CakeTestFixture {
/**
 * Import
 *
 * @var array
 */
	//public $import = array('records' => true);

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 11, 'key' => 'primary'),
		'program_id' => array('type' => 'string', 'null' => false, 'length' => 36),
		'user_id' => array('type' => 'integer', 'null' => false),
		'indexes' => array(),
		'tableParameters' => array()
	);
	
	public $records = array(
		array(
			'id' => 1,
			'program_id' => '1',
			'user_id' => '1',
		),
		array(
			'id' => 2,
			'program_id' => '2',
			'user_id' => '2',
		),
	);

}
