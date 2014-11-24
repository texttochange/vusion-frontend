<?php
class DATABASE_CONFIG {

	public $test = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'cake_test',
		'password' => 'password',
		'database' => 'vusion_test',
		'encoding' => 'utf8'
	);

	public $default = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'cake',
		'password' => 'password',
		'database' => 'vusion',
		'encoding' => 'utf8'
	);
	
	public $vusion = array(
		'datasource' => 'Mongodb.MongodbSource',
		'persistent' => false,
		'host' => 'localhost',
		'database' => 'vusion',
		'port' => '27017',
	);

	public $test_vusion = array(
		'datasource' => 'Mongodb.MongodbSource',
		'persistent' => false,
		'host' => 'localhost',
		'database' => 'test_vusion',
		'port' => '27017',
	);

	public $mongo_program_specific = array(
		'datasource' => 'Mongodb.MongodbSource',
		'persistent' => false,
		'host' => 'localhost',
		'port' => '27017'
	);
	
	public $test_mongo_program_specific = array(
		'datasource' => 'Mongodb.MongodbSource',
		'persistent' => false,
		'database' => 'cake_test',
		'host' => 'localhost',
		'port' => '27017'	
	);
	
}
