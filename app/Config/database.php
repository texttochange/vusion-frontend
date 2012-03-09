<?php
class DATABASE_CONFIG {

	public $test = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'caketest',
		'password' => 'password',
		'database' => 'cake_poc_test',
		'encoding' => 'utf8'
	);
	public $default = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'cake',
		'password' => 'password',
		'database' => 'cake_poc',
		'encoding' => 'utf8'
	);
	
	var $mongo = array(
		'datasource' => 'Mongodb.MongodbSource',
		'persistent' => false,
		'host' => 'localhost',
		'port' => '27017'
	);
	
	var $mongo_test = array(
		'datasource' => 'Mongodb.MongodbSource',
		'persistent' => false,
		'database' => 'cake_test',
		'host' => 'localhost',
		'port' => '27017'	
	);
	
}
