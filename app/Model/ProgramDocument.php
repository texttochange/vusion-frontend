<?php
App::uses('MongoModel', 'Model');
/**
 * Program Model
 *
 */
class ProgramDocument extends MongoModel {

	var $specific = true;	
/**
 * Display field
 *
 * @var string
 */
 	var $name = 'ProgramDocument';
	var $useDbConfig = 'mongo';
	
}
