<?php
/* User Fixture generated on: 2012-01-24 15:34:07 : 1327408447 */

/**
 * UserFixture
 *
 */
class UserFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 11, 'key' => 'primary'),
		'username' => array('type' => 'string', 'null' => false),
		'password' => array('type' => 'string', 'null' => false, 'length' => 40),
		'group_id' => array('type' => 'integer', 'null' => false),
		'limited_program_access' => array('type' => 'boolean', 'null' => false),
		'created' => array('type' => 'datetime', 'null' => true),
		'modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array('users_username_key' => array('unique' => true, 'column' => 'username')),
		'tableParameters' => array()
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'username' => 'Lorem ipsum dolor sit amet',
			'password' => 'Lorem ipsum dolor sit amet',
			'group_id' => 1,
			'limited_program_access' => 1,
			'created' => '2012-01-24 15:34:07',
			'modified' => '2012-01-24 15:34:07'
		),
	);
}
