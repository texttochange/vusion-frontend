<?php
App::uses('MongoModel', 'Model');
/**
 * Program Model
 *
 */
class Participant extends MongoModel {

	var $specific = true;	
/**
 * Display field
 *
 * @var string
 */
 	var $name = 'Participant';
	var $useDbConfig = 'mongo';
	
}
