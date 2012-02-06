<?php 

/** Fixture only work as SQL substitute cannot make it work 
*
*/
class ScriptFixture extends CakeTestFixture {
	
	public $useDbConfig = 'mongo_test';
	//var $name = 'GroupTest'
	public $fields = array(
		'id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'_id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'length' => 50),
		'activated' => array('type' => 'boolean', 'null' => true)
		);
	
	public $records = array(
		array(
			'_id' => 'id',
			'name' => 'Some script',
			'activated' => false
		)
	);
	
}


?>
