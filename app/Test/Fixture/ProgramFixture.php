<?php
/* Program Fixture generated on: 2012-01-24 15:29:24 : 1327408164 */

/**
 * ProgramFixture
 *
 */
class ProgramFixture extends CakeTestFixture {

	
	public $useDbConfig = 'test';
/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'length' => 50),
		'url' => array('type' => 'string', 'null' => true, 'length' => 20),
		'database' => array('type' => 'string', 'null' => true, 'length' => 20),
		'created' => array('type' => 'datetime', 'null' => true),
		'modified' => array('type' => 'datetime', 'null' => true),
		'indexes' => array(),
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
			'name' => 'test',
			'url' => 'test',
			'database' => 'test',
			'created' => '2012-01-24 15:29:24',
			'modified' => '2012-01-24 15:29:24'
		),
		array(
			'id' => 2,
			'name' => 'm6h',
			'url' => 'm6h',
			'database' => 'm6h',			
			'created' => '2012-01-24 15:29:24',
			'modified' => '2012-01-24 15:29:24'
		),
	);
}
