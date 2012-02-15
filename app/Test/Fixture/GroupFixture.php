<?php 

class GroupFixture extends CakeTestFixture {
	
	public $useDbConfig = 'test';
	//var $name = 'GroupTest';
	var $import = array(
		'table' => 'groups'
		);
	
	public $records = array(
		array(
			'id' => 1,
			'name' => 'Admin Group',
			'specific_program_access' => false
		),
		array(
			'id' => 2,
			'name' => 'Program Manager Group',
			'specific_program_access' => true
		)
	);
	
}


?>
